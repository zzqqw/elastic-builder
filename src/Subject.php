<?php

namespace Xqiang\ElasticBuilder;

use Elasticsearch\ClientBuilder;
use Xqiang\ElasticBuilder\Searcher\BasicSearcher;
use Xqiang\ElasticBuilder\Searcher\DateHistogramSearcher;
use Xqiang\ElasticBuilder\Searcher\TermsSearcher;
use Xqiang\ElasticBuilder\Elastic\ElasticBase;
use Xqiang\ElasticBuilder\Elastic\QueryDSL;

//  定义主题基础类
//   一个主题就是一个Elastic索引
//  1.重写setClient方法进行连接Elasticsearch
//  2.定义index 索引名称（索引别名）
//  3.如果需要全局需要进行过滤条件,可以如下操作
//  public function __construct(){
//     $this->where('sex',1);
//     parent::__construct();
//  }
//
abstract class Subject
{
    use QueryDSL, ElasticBase;

    /**
     * @var array
     */
    public $indicators = [];
    /**
     * @var int|string
     */
    public $startTime = 0;
    /**
     * @var int|string
     */
    public $endTime = 0;

    /**
     * @var array
     */
    public static $host = [
        '127.0.0.1:9200'
    ];

    /**
     * @return void;
     */
    public function __construct()
    {
        $this->setClient();
    }

    /**
     * @return void
     */
    protected function setClient()
    {
        $this->client = ClientBuilder::create()
            ->setHosts(static::$host)
            ->setSSLVerification(false)->build();
    }

    /**
     * @return void
     */
    protected function setParams()
    {
        $this->params['index'] = $this->index;
        $this->params['type']  = $this->type;
        if ($query = $this->getQuery()) {
            $this->params['body']['query'] = $query;
        }
    }

    /**
     * @param array $aggs
     * @return $this
     */
    public function aggs(array $aggs)
    {
        $this->params['body']['aggs'] = $aggs;
        return $this;
    }

    /**
     * @return array
     */
    public function search()
    {
        return $this->officialSearch();
    }

    /**
     * @param array $indicators
     * @return  $this
     */
    public function setIndicators(array $indicators)
    {
        $this->indicators = array_merge($this->indicators, $indicators);
        return $this;
    }

    /**
     * @param $startTime
     * @param $endTime
     * @return $this
     */
    public function setTime($startTime, $endTime)
    {
        $this->startTime = $startTime;
        $this->endTime   = $endTime;
        return $this;
    }

    /**
     * @return BasicSearcher
     */
    public function searchBasicData()
    {
        $this->getParams();
        return (new BasicSearcher($this))
            ->search();
    }

    /**
     * @param string $field
     * @param int $size
     * @return TermsSearcher
     */
    public function searchSingleTerms($field, $size = 20)
    {
        return $this->searchTerms([$field => ['size' => $size]]);
    }

    /**
     * @param array $fields
     * ['name'=>['size'=>2],'sex'=>['size'=>1]]
     * ['name','sex']
     * ['name','sex'=>['size'=>1]]
     * @return TermsSearcher
     */
    public function searchTerms(array $fields)
    {
        $this->getParams();
        return (new TermsSearcher($this))
            ->setFields($fields)
            ->search();
    }

    /**
     * year（1y）年
     * quarter（1q）季度
     * month（1M）月份
     * week（1w）星期
     * day（1d）天
     * hour（1h）小时
     * minute（1m）分钟
     * second（1s）秒
     * @param string $interval
     * @param array $order
     * @return DateHistogramSearcher
     */
    public function searchDateHistogram($interval = '1d', $order = [])
    {
        $this->getParams();
        return (new DateHistogramSearcher($this))
            ->interval($interval)
            ->order($order)
            ->search();
    }
}