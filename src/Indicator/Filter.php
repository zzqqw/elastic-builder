<?php

namespace Xqiang\ElasticBuilder\Indicator;

use Xqiang\ElasticBuilder\Elastic\QueryDSL;

class Filter
{
    use QueryDSL;

    /**
     * @param array $query
     * @return $this
     */
    public function setQuery(array $query)
    {
        $this->query = $query;
        return $this;
    }
}