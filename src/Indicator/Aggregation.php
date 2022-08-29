<?php

namespace Xqiang\ElasticBuilder\Indicator;

class Aggregation
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $field;
    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    const COUNT = 'value_count';
    /**
     * @var string
     */
    const SUM = 'sum';
    /**
     * @var string
     */
    const AVG = 'avg';
    /**
     * @var string
     */
    const MAX = 'max';
    /**
     * @var string
     */
    const MIN = 'min';

    /**
     * @param $name
     * @param $field
     * @param $type
     */
    public function __construct($name, $field, $type)
    {
        $this->name  = $name;
        $this->field = $field;
        $this->type  = $type;
    }

    /**
     * @return array[]
     */
    public function getAggregation()
    {
        return [
            $this->type => [
                'field' => $this->field
            ]
        ];
    }
}