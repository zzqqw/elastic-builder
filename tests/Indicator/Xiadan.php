<?php

namespace Xqiang\ElasticBuilder\Tests\Indicator;

use Xqiang\ElasticBuilder\Indicator;

class Xiadan extends Indicator
{
    /**
     * @var string
     */
    public static $name = 'xiadan_count';
    /**
     * @var string
     */
    public static $timeField = 'clearing_time';
}