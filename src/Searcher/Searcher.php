<?php

namespace Xqiang\ElasticBuilder\Searcher;

use Xqiang\ElasticBuilder\Indicator;
use Xqiang\ElasticBuilder\Subject;
use Xqiang\ElasticBuilder\Utils\Arr;

abstract class Searcher
{
    /**
     * @var Subject
     */
    protected $subject;

    /**
     * @var array
     */
    protected $searchResult = [];
    /**
     * @var array
     */
    protected $params = [];

    /**
     * @var int
     */
    protected $size = 0;

    /**
     * @var array
     */
    protected $indirects = [];

    /**
     * @var array
     */
    protected $resaultIndirects = [];
    /**
     * @var array
     */
    protected $aggs = [];

    /**
     * @param Subject $subject
     */
    public function __construct(Subject $subject)
    {
        $this->subject                = $subject;
        $this->params                 = $this->subject->getParams();
        $this->params['body']['size'] = $this->size;
        $this->setAllIndicators($this->subject->indicators);
    }

    /**
     * 加载所有的指标
     * @param array $indicators
     * @return void
     */
    protected function setAllIndicators(array $indicators)
    {
        foreach ($indicators as $indicator) {
            if (!is_subclass_of($indicator, Indicator::class)) {
                throw new \RuntimeException("$indicator not extends \Xqiang\ElasticBuilder\Indicator");
            }
            if (empty($indicator::$timeField)) {
                $this->resaultIndirects[crc32($indicator)] = $indicator;
            } else {
                $this->indirects[crc32($indicator)] = $indicator;
            }
            if ($relates = $indicator::$relate) {
                foreach ($relates as $relate) {
                    if (!is_subclass_of($relate, Indicator::class)) {
                        throw new \RuntimeException("$relate not extends \Xqiang\ElasticBuilder\Indicator");
                    }
                    if (empty($relate::$timeField)) {
                        $this->resaultIndirects[crc32($relate)] = $relate;
                    } else {
                        $this->indirects[crc32($relate)] = $relate;
                    }
                }
            }
        }
    }

    /**
     * @return void
     */
    abstract protected function buildAggs();

    /**
     * @return void
     */
    abstract public function result();

    /**
     * @return array
     */
    public function params()
    {
        $this->buildAggs();
        if ($aggs = $this->aggs) {
            $this->params['body']['aggs'] = $aggs;
        }
        return $this->params;
    }

    /**
     * @return $this
     */
    public function search()
    {
        $params             = $this->params();
        $this->searchResult = $this->subject->officialSearch($params);
        return $this;
    }

    /**
     * @return array
     */
    public function getSearchResult()
    {
        return $this->searchResult;
    }


    /**
     * 打乱字段重新生成数组
     * @param array $fields
     * @return array
     */
    protected function resetFields($fields)
    {
        foreach ($fields as $field => &$parameter) {
            if (is_array($parameter)) {
                $parameter['field'] = $field;
            }
            if (is_string($parameter)) {
                $parameter = ['field' => $parameter];
            }
        }
        return $fields;
    }


    /**
     * 范围过滤表达式
     * @param $field
     * @param $gte
     * @param $lte
     * @return array
     */
    protected function filterRange($field, $gte, $lte)
    {
        $query['bool']['must'][] = [
            'range' => [
                $field => [
                    'gte' => $gte,
                    'lte' => $lte,
                ]
            ]
        ];
        return $query;
    }

    /**
     * 基于时间indirects生成aggs DSL
     * @return array
     */
    public function indirectsTimeFieldAggs()
    {
        $returnAggs = [];
        if ($indirects = $this->indirects) {
            $timeIndirects = Arr::index($indirects, function ($indirect) {
                /** @var Indicator $indirect */
                return $indirect::$timeField;
            });
            foreach ($timeIndirects as $timeField => $indirects) {
                $returnAggs[$timeField]['filter'] = $this->filterRange($timeField, $this->subject->startTime, $this->subject->endTime);
                $indirectAggs                     = [];
                /** @var Indicator $indirect */
                foreach ($indirects as $indirect) {
                    $indirectAggs[$indirect::$name] = $indirect::aggs()->getAggs();
                }
                $returnAggs[$timeField]['aggs'] = $indirectAggs;
            }
        }
        return $returnAggs;
    }

    /**
     * 向重置后的字段插入IndicatorsAggs
     * @param array $resetFields
     * @param array $pushAggs
     * @return array|mixed
     */
    public function fieldsPushIndicatorsAggs($resetFields, array $pushAggs)
    {
        if (empty($resetFields)) {
            return $pushAggs;
        }
        $fields = array_values($resetFields);
        //找到最后一个字段
        $maxDepth = max(array_keys($fields));
        $aggs     = &$result;
        foreach ($fields as $level => $field) {
            $tempLevel = $level;
            while ($level--) {
                if (!isset($aggs[key($aggs)]['aggs'])) {
                    $aggs[key($aggs)]['aggs'] = [];
                }
                $aggs = &$aggs[key($aggs)]['aggs'];
            }
            $aggs[$field['field']]['terms'] = $field;
            if ($tempLevel === $maxDepth && !empty($pushAggs)) {
                $aggs[$field['field']]['aggs'] = $pushAggs;
            }
            unset($tempLevel);
            $aggs = &$result;
        }
        return $aggs;
    }


    /**
     * 递归处理aggregations数据
     * @param array $aggregations
     * @param $result
     * @param array $fields
     * @param int $depth
     * @return void
     */
    protected function recursionResult(array $aggregations, &$result, array $fields, $depth = 0)
    {
        foreach ($aggregations['buckets'] as $aggregation) {
            $endResult = [];
            if (isset($aggregation['key']) && $intersectFields = array_intersect(array_keys($aggregation), $fields)) {
                $field = reset($intersectFields);
                foreach ($aggregation[$field]['buckets'] as $item) {
                    $endResult[$item['key']] = [];
                }
                $this->recursionResult($aggregation[$field], $endResult, $fields, $depth + 1);
            } else {
                $endResult = $this->indirectsResult($aggregation);
            }
            $key          = $aggregation['key'];
            $result[$key] = $endResult;
        }
    }

    /**
     * 解析$aggregations数据
     * @param array $aggregations
     * @return array
     */
    protected function indirectsResult(array $aggregations)
    {
        $result = [];
        foreach ($aggregations as $timeField => $aggregation) {
            if (is_array($aggregation)) {
                foreach ($aggregation as $k => $val) {
                    if (!is_array($val)) {
                        $result[$timeField . '_' . $k] = $val;
                    } else {
                        $result[$k] = isset($val[$k]['value']) ? $val[$k]['value'] : (isset($val['value']) ? $val['value'] : $val['doc_count']);
                    }
                }
            }
        }
        /** @var Indicator $indirect */
        if ($resultIndirects = $this->resaultIndirects) {
            foreach ($resultIndirects as $indirect) {
                $result[$indirect::$name] = $indirect::resault($result);
            }
        }
        return $result;
    }

    /**
     * DateHistogramSearcher 在使用
     * @param array $bucket
     * @return array
     */
    protected function indirectsResultBucket(array $bucket)
    {
        $ret = [];
        foreach ($bucket as $k => $val) {
            if (is_array($val)) {
                $ret[$k] = isset($val[$k]['value']) ? $val[$k]['value'] : (isset($val['value']) ? $val['value'] : $val['doc_count']);
            } else {
                $ret[$k] = $val;
            }
        }
        /** @var Indicator $indirect */
        if ($resultIndirects = $this->resaultIndirects) {
            foreach ($resultIndirects as $indirect) {
                $ret[$indirect::$name] = $indirect::resault($ret);
            }
        }
        return $ret;
    }
}