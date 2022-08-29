<?php

namespace Xqiang\ElasticBuilder\Tests\Indicator;

use Xqiang\ElasticBuilder\Indicator;
use Xqiang\ElasticBuilder\Utils\Number;

class CompletionRate extends Indicator
{
    public static $name = 'completion_rate';

    public static $relate = [
        Ruku::class, Xiadan::class,
    ];

    public static function resault(array $resault = [])
    {
        return Number::percent(
            $resault[Ruku::$name], $resault[Xiadan::$name]
        );
    }
}