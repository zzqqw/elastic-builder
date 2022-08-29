<?php

namespace Xqiang\ElasticBuilder\Searcher;

class TermsSearcher extends Searcher
{
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
     * @return void
     */
    protected function buildAggs()
    {
        $this->aggs = $this->fieldsPushIndicatorsAggs(
            $this->fields,
            $this->indirectsTimeFieldAggs()
        );
    }

    /**
     * @return array
     */
    public function result()
    {
        $aggregations = isset($this->searchResult['aggregations']) ? $this->searchResult['aggregations'] : [];
        $fields       = $this->fields ? array_column($this->fields, 'field') : [];
        $result       = [];
        $this->recursionResult($aggregations[reset($fields)], $result, $fields);
        return $result;
    }
}