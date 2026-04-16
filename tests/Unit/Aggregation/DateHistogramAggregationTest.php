<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use PHPUnit\Framework\Attributes\DependsExternal;
use Bonu\ElasticsearchBuilder\Tests\Fixture\BoolQueryFixture;
use Bonu\ElasticsearchBuilder\Aggregation\DateHistogramAggregation;
use Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation\Trait\FilterableAggregationTest;
use Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation\Trait\GlobalizableAggregationTest;
use Bonu\ElasticsearchBuilder\Exception\Aggregation\InvalidDateHistogramIntervalException;

/**
 * @internal
 */
final class DateHistogramAggregationTest extends TestCase
{
    #[Test]
    public function itBuildsWithCalendarInterval(): void
    {
        $agg = new DateHistogramAggregation('sales_over_time', 'date', calendarInterval: 'month');

        $this->assertSame([
            'sales_over_time' => [
                'date_histogram' => [
                    'field' => 'date',
                    'calendar_interval' => 'month',
                ],
            ],
        ], $agg->toArray());
    }

    #[Test]
    public function itBuildsWithFixedInterval(): void
    {
        $agg = new DateHistogramAggregation('sales_over_time', 'date', fixedInterval: '30d');

        $this->assertSame([
            'sales_over_time' => [
                'date_histogram' => [
                    'field' => 'date',
                    'fixed_interval' => '30d',
                ],
            ],
        ], $agg->toArray());
    }

    #[Depends('itBuildsWithCalendarInterval')]
    #[Test]
    public function itBuildsWithAllOptionalParameters(): void
    {
        $agg = new DateHistogramAggregation(
            'sales_over_time',
            'date',
            calendarInterval: 'month',
            minDocCount: 1,
            format: 'yyyy-MM-dd',
            timeZone: 'Europe/Prague',
            offset: '+6h',
        );

        $this->assertSame([
            'sales_over_time' => [
                'date_histogram' => [
                    'field' => 'date',
                    'calendar_interval' => 'month',
                    'min_doc_count' => 1,
                    'format' => 'yyyy-MM-dd',
                    'time_zone' => 'Europe/Prague',
                    'offset' => '+6h',
                ],
            ],
        ], $agg->toArray());
    }

    #[Test]
    public function itThrowsExceptionWhenNoIntervalProvided(): void
    {
        $this->expectException(InvalidDateHistogramIntervalException::class);

        new DateHistogramAggregation('sales_over_time', 'date');
    }

    #[Test]
    public function itThrowsExceptionWhenBothIntervalsProvided(): void
    {
        $this->expectException(InvalidDateHistogramIntervalException::class);

        new DateHistogramAggregation('sales_over_time', 'date', calendarInterval: 'month', fixedInterval: '30d');
    }

    #[Depends('itBuildsWithCalendarInterval')]
    #[DependsExternal(GlobalizableAggregationTest::class, 'itAddsGlobalToAggregation')]
    #[Test]
    public function itCanBeGlobal(): void
    {
        $agg = new DateHistogramAggregation('sales_over_time', 'date', calendarInterval: 'month');
        $agg = $agg->asGlobal();

        $this->assertEquals([
            'sales_over_time' => [
                'global' => (object) [],
                'aggs' => [
                    'sales_over_time' => [
                        'date_histogram' => [
                            'field' => 'date',
                            'calendar_interval' => 'month',
                        ],
                    ],
                ],
            ],
        ], $agg->toArray());
    }

    #[Depends('itBuildsWithCalendarInterval')]
    #[DependsExternal(FilterableAggregationTest::class, 'itAddsFilterToAggregation')]
    #[Test]
    public function itCanBeFiltered(): void
    {
        $agg = new DateHistogramAggregation('sales_over_time', 'date', calendarInterval: 'month');
        $agg = $agg->query(new BoolQueryFixture('foo'));

        $this->assertSame([
            'sales_over_time' => [
                'filter' => [
                    'foo' => 'fixture_for_bool_query',
                ],
                'aggs' => [
                    'sales_over_time' => [
                        'date_histogram' => [
                            'field' => 'date',
                            'calendar_interval' => 'month',
                        ],
                    ],
                ],
            ],
        ], $agg->toArray());
    }

    #[Depends('itBuildsWithCalendarInterval')]
    #[Depends('itCanBeFiltered')]
    #[Depends('itCanBeGlobal')]
    #[Test]
    public function itCanBeGlobalAndFilteredTogether(): void
    {
        $agg = new DateHistogramAggregation('sales_over_time', 'date', fixedInterval: '7d');
        $agg = $agg->asGlobal()->query(new BoolQueryFixture('foo'));

        $this->assertEquals([
            'sales_over_time' => [
                'global' => (object) [],
                'aggs' => [
                    'sales_over_time' => [
                        'filter' => [
                            'foo' => 'fixture_for_bool_query',
                        ],
                        'aggs' => [
                            'sales_over_time' => [
                                'date_histogram' => [
                                    'field' => 'date',
                                    'fixed_interval' => '7d',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], $agg->toArray());
    }

    #[Test]
    public function itReturnsCorrectName(): void
    {
        $agg = new DateHistogramAggregation('sales_over_time', 'date', calendarInterval: 'day');
        $this->assertSame('sales_over_time', $agg->getName());
    }
}
