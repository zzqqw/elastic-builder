<?php

namespace Xqiang\ElasticBuilder\Indicator;


class IndicatorAggs
{
    /**
     * @var
     */
    public $name;

    /**
     * @var Filter
     */
    public $filter;
    /**
     * @var Aggregation
     */
    public $aggregation;

    /**
     * @param $name
     * @param Filter|null $filter
     * @param Aggregation|null $aggregation
     */
    public function __construct($name, Filter $filter = null, Aggregation $aggregation = null)
    {
        $this->name        = $name;
        $this->aggregation = $aggregation;
        $this->filter      = $filter;
        if (is_null($this->aggregation) && is_null($this->filter)) {
            throw new \InvalidArgumentException("$name Missing filter && aggregation parameter");
        }
    }

    /**
     * @return array
     */
    public function getAggs()
    {
        $returnAggs = [];
        if (!is_null($filter = $this->filter) && !empty($query = $filter->getQuery())) {
            $returnAggs['filter'] = $query;
            if (!is_null($aggregation = $this->aggregation->getAggregation())) {
                $returnAggs['aggs'][$this->name] = $aggregation;
            }
        } else {
            $returnAggs = $this->aggregation->getAggregation();;
        }
        return $returnAggs;
    }
}