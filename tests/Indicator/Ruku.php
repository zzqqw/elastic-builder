<?php

namespace Xqiang\ElasticBuilder\Tests\Indicator;

use Xqiang\ElasticBuilder\Indicator;

class Ruku extends Indicator
{
    public static $name      = 'ruku_count';
    public static $timeField = 'clearing_time';

    public static $filterQueryDSL = [
        'bool' => [
            'must' => [
                [
                    'match_phrase' => [
                        'type' => 2
                    ]
                ]
            ]
        ]
    ];
}