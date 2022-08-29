<?php

namespace Xqiang\ElasticBuilder\Searcher;


class BasicSearcher extends Searcher
{
    /**
     * @return void
     */
    protected function buildAggs()
    {
        $this->aggs = $this->indirectsTimeFieldAggs();
    }

    /**
     * @return array
     */
    public function result()
    {
        $aggregations = isset($this->searchResult['aggregations']) ? $this->searchResult['aggregations'] : [];
        return $this->indirectsResult($aggregations);
    }
}