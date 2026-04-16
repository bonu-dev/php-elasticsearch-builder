<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use PHPUnit\Framework\Attributes\DependsExternal;
use Bonu\ElasticsearchBuilder\Aggregation\SumAggregation;
use Bonu\ElasticsearchBuilder\Tests\Fixture\BoolQueryFixture;
use Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation\Trait\FilterableAggregationTest;
use Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation\Trait\GlobalizableAggregationTest;

/**
 * @internal
 */
final class SumAggregationTest extends TestCase
{
    #[Test]
    public function itBuildsBasicSumAggregation(): void
    {
        $agg = new SumAggregation('total_price', 'price');

        $this->assertSame([
            'total_price' => [
                'sum' => [
                    'field' => 'price',
                ],
            ],
        ], $agg->toArray());
    }

    #[Depends('itBuildsBasicSumAggregation')]
    #[DependsExternal(GlobalizableAggregationTest::class, 'itAddsGlobalToAggregation')]
    #[Test]
    public function itCanBeGlobal(): void
    {
        $agg = new SumAggregation('total_price', 'price');
        $agg = $agg->asGlobal();

        $this->assertEquals([
            'total_price' => [
                'global' => (object) [],
                'aggs' => [
                    'total_price' => [
                        'sum' => [
                            'field' => 'price',
                        ],
                    ],
                ],
            ],
        ], $agg->toArray());
    }

    #[Depends('itBuildsBasicSumAggregation')]
    #[DependsExternal(FilterableAggregationTest::class, 'itAddsFilterToAggregation')]
    #[Test]
    public function itCanBeFiltered(): void
    {
        $agg = new SumAggregation('total_price', 'price');
        $agg = $agg->query(new BoolQueryFixture('foo'));

        $this->assertSame([
            'total_price' => [
                'filter' => [
                    'foo' => 'fixture_for_bool_query',
                ],
                'aggs' => [
                    'total_price' => [
                        'sum' => [
                            'field' => 'price',
                        ],
                    ],
                ],
            ],
        ], $agg->toArray());
    }

    #[Depends('itBuildsBasicSumAggregation')]
    #[Depends('itCanBeFiltered')]
    #[Depends('itCanBeGlobal')]
    #[Test]
    public function itCanBeGlobalAndFilteredTogether(): void
    {
        $agg = new SumAggregation('total_price', 'price');
        $agg = $agg->asGlobal()->query(new BoolQueryFixture('foo'));

        $this->assertEquals([
            'total_price' => [
                'global' => (object) [],
                'aggs' => [
                    'total_price' => [
                        'filter' => [
                            'foo' => 'fixture_for_bool_query',
                        ],
                        'aggs' => [
                            'total_price' => [
                                'sum' => [
                                    'field' => 'price',
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
        $agg = new SumAggregation('total_price', 'price');
        $this->assertSame('total_price', $agg->getName());
    }
}
