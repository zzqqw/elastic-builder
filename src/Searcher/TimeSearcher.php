<?php

namespace Xqiang\ElasticBuilder\Searcher;

class TimeSearcher extends Searcher
{
    /**
     * @var array
     */
    protected $date;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $fields;

    /**
     * @param array $fields
     * @return $this
     */
    public function setFields(array $fields)
    {
        $this->fields = $this->resetFields($fields);
        return $this;
    }

    /**
     * @param $name
     * @param array $date
     * @return $this
     */
    public function date($name, array $date)
    {
        $this->name = $name;
        $this->date = $date;
        return $this;
    }

    /**
     * @return void
     */
    protected function buildAggs()
    {
        $this->aggs[$this->name]         = $this->date;
        $this->aggs[$this->name]['aggs'] = $this->fieldsPushIndicatorsAggs(
            $this->indirectsTimeFieldAggs(),
            $this->fields
        );
    }

    /**
     * @return array
     */
    public function result()
    {
        $aggregations = $this->searchResult['aggregations'];
        $fields       = $this->fields ? array_column($this->fields, 'field') : [];
        $result       = [];
        $this->recursionResult($aggregations[$this->name], $result, $fields);
        return $result;
    }
}