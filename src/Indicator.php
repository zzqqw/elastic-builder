<?php

namespace Xqiang\ElasticBuilder;

use Xqiang\ElasticBuilder\Indicator\Aggregation;
use Xqiang\ElasticBuilder\Indicator\Filter;
use Xqiang\ElasticBuilder\Indicator\IndicatorAggs;

/**
 * 定义指标基础类
 */
abstract class Indicator
{
    /**
     * 定义指标的名称，如：下单量
     * 多个指标名称一定不能出现相同的名称
     * @var string
     */
    public static $name = 'id_count';

    /**
     * 定义时间字段，在搜索的时候要按照 不同的指标时间维度不一样。
     * 如果你没有定义时间维度，就不会发送数据到Elasticsearch进行统计，反而走resault方法中的值做为该指标的值
     * @var string
     */
    public static $timeField = '';

    /**
     * 聚合统计字段
     * @var string
     */
    public static $field = 'id';

    /**
     * 聚合统计方法 value_count,sum,avg,max,min
     * @var string
     */
    public static $type = Aggregation::COUNT;

    /**
     * 指标过滤条件筛选
     * @var array
     */
    public static $filterQueryDSL = [];

    /**
     * 子指标集合
     * 一般用于resault方法中
     * @var array
     */
    public static $relate = [];

    /**
     * 通过该方法获取该指标DSL，可参考重写
     * @return IndicatorAggs
     */
    public static function aggs()
    {
        $filter = new Filter();
        $filter->setQuery(static::$filterQueryDSL);
        // 当第三个参数为null时，读取过滤条件doc_count为当前指标当值
        return new IndicatorAggs(
            static::$name,
            $filter,
            new Aggregation(static::$name, static::$field, static::$type)
        );
    }

    /**
     * 通过该方法可以重新计算一个指标
     * @param array $resault
     * @return int|string
     */
    public static function resault(array $resault = [])
    {
        return 0;
    }
}