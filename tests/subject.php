<?php

use Xqiang\ElasticBuilder\Tests\Indicator\CompletionRate;
use Xqiang\ElasticBuilder\Tests\OrderSubject;

require 'vendor/autoload.php';

$o = new OrderSubject();
$o->setTime(strtotime('2022-07-01'), strtotime('2022-07-30'));
$o->setIndicators([CompletionRate::class]);

//var_dump($o->searchBasicData()->result());
//var_dump($o->searchDateHistogram()->result());
//var_dump($o->searchTerms(['id'])->result());