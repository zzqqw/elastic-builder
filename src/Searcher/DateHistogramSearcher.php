<?php

namespace Xqiang\ElasticBuilder\Searcher;

use Xqiang\ElasticBuilder\Indicator;
use Xqiang\ElasticBuilder\Utils\Arr;

class DateHistogramSearcher extends Searcher
{
    /**
     * @var string
     */
    protected $interval;
    /**
     * @var array
     */
    protected $order = [];

    /**
     * @param $interval
     * @return $this
     */
    public function interval($interval)
    {
        $this->interval = $interval;
        return $this;
    }

    /**
     * @param array $order
     * @return $this
     */
    public function order(array $order = [])
    {
        $this->order = !empty($order) ? $order : ['_key' => 'desc'];
        return $this;
    }

    /**
     * @return void
     */
    protected function buildAggs()
    {
        $returnAggs = [];
        if ($indirects = $this->indirects) {
            $timeIndirects = Arr::index($indirects, function ($indirect) {
                /** @var Indicator $indirect */
                return $indirect::$timeField;
            });
            foreach ($timeIndirects as $timeField => $indirects) {
                $returnAggs[$timeField]['filter'] = $this->filterRange($timeField, $this->subject->startTime, $this->subject->endTime);
                $indirectAgg                      = [];
                /** @var Indicator $indirect */
                foreach ($indirects as $indirect) {
                    $indirectAgg[$indirect::$name] = $indirect::aggs()->getAggs();
                }
                $returnAggs[$timeField]['aggs'][$timeField]         = [
                    'date_histogram' => [
                        'field'         => $timeField,
                        'interval'      => $this->interval,
                        'time_zone'     => date_default_timezone_get(),
                        'min_doc_count' => 0,
                        'order'         => $this->order
                    ]
                ];
                $returnAggs[$timeField]['aggs'][$timeField]['aggs'] = $indirectAgg;
            }
        }
        $this->aggs = $returnAggs;
    }

    /**
     * @return array
     */
    public function result()
    {
        $aggregations = isset($this->searchResult['aggregations']) ? $this->searchResult['aggregations'] : [];
        $resault      = [];
        foreach ($aggregations as $timeField => $aggregation) {
            $buckets = !empty($aggregation[$timeField]['buckets']) ? $aggregation[$timeField]['buckets'] : [];
            if ($buckets) {
                foreach ($buckets as $bucket) {
                    $key_as_string           = $bucket['key_as_string'];
                    $resault[$key_as_string] = !empty($resault[$key_as_string]) ? $resault[$key_as_string] : [];
                    $resault[$key_as_string] = array_merge($resault[$key_as_string], $this->indirectsResultBucket($bucket));
                }
            }
        }
        return $resault;
    }
}