<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use PHPUnit\Framework\Attributes\DependsExternal;
use Bonu\ElasticsearchBuilder\Tests\Fixture\BoolQueryFixture;
use Bonu\ElasticsearchBuilder\Aggregation\HistogramAggregation;
use Bonu\ElasticsearchBuilder\Exception\Aggregation\InvalidIntervalException;
use Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation\Trait\FilterableAggregationTest;
use Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation\Trait\GlobalizableAggregationTest;

/**
 * @internal
 */
final class HistogramAggregationTest extends TestCase
{
    #[Test]
    public function itBuildsBasicHistogramAggregation(): void
    {
        $agg = new HistogramAggregation('prices', 'price');

        $this->assertSame([
            'prices' => [
                'histogram' => [
                    'field' => 'price',
                    'interval' => 10,
                ],
            ],
        ], $agg->toArray());
    }

    #[Depends('itBuildsBasicHistogramAggregation')]
    #[Test]
    public function itBuildsWithCustomIntervalAndMinDocCount(): void
    {
        $agg = new HistogramAggregation('prices', 'price', 50, 1);

        $this->assertSame([
            'prices' => [
                'histogram' => [
                    'field' => 'price',
                    'interval' => 50,
                    'min_doc_count' => 1,
                ],
            ],
        ], $agg->toArray());
    }

    #[Test]
    public function itThrowsExceptionForInvalidInterval(): void
    {
        $this->expectException(InvalidIntervalException::class);

        new HistogramAggregation('prices', 'price', 0);
    }

    #[Test]
    public function itThrowsExceptionForNegativeInterval(): void
    {
        $this->expectException(InvalidIntervalException::class);

        new HistogramAggregation('prices', 'price', -5);
    }

    #[Depends('itBuildsBasicHistogramAggregation')]
    #[DependsExternal(FilterableAggregationTest::class, 'itAddsFilterToAggregation')]
    #[Test]
    public function itCanBeFiltered(): void
    {
        $agg = new HistogramAggregation('prices', 'price');
        $agg = $agg->query(new BoolQueryFixture('foo'));

        $this->assertSame([
            'prices' => [
                'filter' => [
                    'foo' => 'fixture_for_bool_query',
                ],
                'aggs' => [
                    'prices' => [
                        'histogram' => [
                            'field' => 'price',
                            'interval' => 10,
                        ],
                    ],
                ],
            ],
        ], $agg->toArray());
    }

    #[Depends('itBuildsBasicHistogramAggregation')]
    #[DependsExternal(GlobalizableAggregationTest::class, 'itAddsGlobalToAggregation')]
    #[Test]
    public function itCanBeGlobal(): void
    {
        $agg = new HistogramAggregation('prices', 'price');
        $agg = $agg->asGlobal();

        $this->assertEquals([
            'prices' => [
                'global' => (object) [],
                'aggs' => [
                    'prices' => [
                        'histogram' => [
                            'field' => 'price',
                            'interval' => 10,
                        ],
                    ],
                ],
            ],
        ], $agg->toArray());
    }

    #[Depends('itBuildsBasicHistogramAggregation')]
    #[Depends('itCanBeFiltered')]
    #[Depends('itCanBeGlobal')]
    #[Test]
    public function itCanBeGlobalAndFilteredTogether(): void
    {
        $agg = new HistogramAggregation('prices', 'price', 20);
        $agg = $agg->asGlobal()->query(new BoolQueryFixture('foo'));

        $this->assertEquals([
            'prices' => [
                'global' => (object) [],
                'aggs' => [
                    'prices' => [
                        'filter' => [
                            'foo' => 'fixture_for_bool_query',
                        ],
                        'aggs' => [
                            'prices' => [
                                'histogram' => [
                                    'field' => 'price',
                                    'interval' => 20,
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
        $agg = new HistogramAggregation('prices', 'price');
        $this->assertSame('prices', $agg->getName());
    }
}
