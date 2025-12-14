<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use PHPUnit\Framework\Attributes\DependsExternal;
use Bonu\ElasticsearchBuilder\Aggregation\StatsAggregation;
use Bonu\ElasticsearchBuilder\Tests\Fixture\BoolQueryFixture;
use Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation\Trait\FilterableAggregationTest;
use Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation\Trait\GlobalizableAggregationTest;

/**
 * @internal
 */
final class StatsAggregationTest extends TestCase
{
    #[Test]
    public function itBuildsBasicStatsAggregation(): void
    {
        $agg = new StatsAggregation('price_stats', 'price');

        $this->assertSame([
            'price_stats' => [
                'stats' => [
                    'field' => 'price',
                ],
            ],
        ], $agg->toArray());
    }

    #[Depends('itBuildsBasicStatsAggregation')]
    #[DependsExternal(GlobalizableAggregationTest::class, 'itAddsGlobalToAggregation')]
    #[Test]
    public function itCanBeGlobal(): void
    {
        $agg = new StatsAggregation('price_stats', 'price');
        $agg = $agg->asGlobal();

        $this->assertEquals([
            'price_stats' => [
                'global' => (object) [],
                'aggs' => [
                    'price_stats' => [
                        'stats' => [
                            'field' => 'price',
                        ],
                    ],
                ],
            ],
        ], $agg->toArray());
    }

    #[Depends('itBuildsBasicStatsAggregation')]
    #[DependsExternal(FilterableAggregationTest::class, 'itAddsFilterToAggregation')]
    #[Test]
    public function itCanBeFiltered(): void
    {
        $agg = new StatsAggregation('price_stats', 'price');
        $agg = $agg->query(new BoolQueryFixture('foo'));

        $this->assertSame([
            'price_stats' => [
                'filter' => [
                    'foo' => 'fixture_for_bool_query',
                ],
                'aggs' => [
                    'price_stats' => [
                        'stats' => [
                            'field' => 'price',
                        ],
                    ],
                ],
            ],
        ], $agg->toArray());
    }

    #[Depends('itBuildsBasicStatsAggregation')]
    #[Depends('itCanBeFiltered')]
    #[Depends('itCanBeGlobal')]
    #[Test]
    public function itCanBeGlobalAndFilteredTogether(): void
    {
        $agg = new StatsAggregation('price_stats', 'price');
        $agg = $agg->asGlobal()->query(new BoolQueryFixture('foo'));

        $this->assertEquals([
            'price_stats' => [
                'global' => (object) [],
                'aggs' => [
                    'price_stats' => [
                        'filter' => [
                            'foo' => 'fixture_for_bool_query',
                        ],
                        'aggs' => [
                            'price_stats' => [
                                'stats' => [
                                    'field' => 'price',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], $agg->toArray());
    }
}
