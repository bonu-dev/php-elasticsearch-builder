<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use PHPUnit\Framework\Attributes\DependsExternal;
use Bonu\ElasticsearchBuilder\Tests\Fixture\BoolQueryFixture;
use Bonu\ElasticsearchBuilder\Aggregation\CardinalityAggregation;
use Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation\Trait\FilterableAggregationTest;
use Bonu\ElasticsearchBuilder\Exception\Aggregation\InvalidPrecisionThresholdException;
use Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation\Trait\GlobalizableAggregationTest;

/**
 * @internal
 */
final class CardinalityAggregationTest extends TestCase
{
    #[Test]
    public function itBuildsBasicCardinalityAggregation(): void
    {
        $agg = new CardinalityAggregation('unique_colors', 'color');

        $this->assertSame([
            'unique_colors' => [
                'cardinality' => [
                    'field' => 'color',
                ],
            ],
        ], $agg->toArray());
    }

    #[Depends('itBuildsBasicCardinalityAggregation')]
    #[Test]
    public function itBuildsWithPrecisionThreshold(): void
    {
        $agg = new CardinalityAggregation('unique_colors', 'color', 100);

        $this->assertSame([
            'unique_colors' => [
                'cardinality' => [
                    'field' => 'color',
                    'precision_threshold' => 100,
                ],
            ],
        ], $agg->toArray());
    }

    #[Test]
    public function itThrowsExceptionForZeroPrecisionThreshold(): void
    {
        $this->expectException(InvalidPrecisionThresholdException::class);

        new CardinalityAggregation('unique_colors', 'color', 0);
    }

    #[Test]
    public function itThrowsExceptionForNegativePrecisionThreshold(): void
    {
        $this->expectException(InvalidPrecisionThresholdException::class);

        new CardinalityAggregation('unique_colors', 'color', -5);
    }

    #[Depends('itBuildsBasicCardinalityAggregation')]
    #[DependsExternal(GlobalizableAggregationTest::class, 'itAddsGlobalToAggregation')]
    #[Test]
    public function itCanBeGlobal(): void
    {
        $agg = new CardinalityAggregation('unique_colors', 'color');
        $agg = $agg->asGlobal();

        $this->assertEquals([
            'unique_colors' => [
                'global' => (object) [],
                'aggs' => [
                    'unique_colors' => [
                        'cardinality' => [
                            'field' => 'color',
                        ],
                    ],
                ],
            ],
        ], $agg->toArray());
    }

    #[Depends('itBuildsBasicCardinalityAggregation')]
    #[DependsExternal(FilterableAggregationTest::class, 'itAddsFilterToAggregation')]
    #[Test]
    public function itCanBeFiltered(): void
    {
        $agg = new CardinalityAggregation('unique_colors', 'color');
        $agg = $agg->query(new BoolQueryFixture('foo'));

        $this->assertSame([
            'unique_colors' => [
                'filter' => [
                    'foo' => 'fixture_for_bool_query',
                ],
                'aggs' => [
                    'unique_colors' => [
                        'cardinality' => [
                            'field' => 'color',
                        ],
                    ],
                ],
            ],
        ], $agg->toArray());
    }

    #[Depends('itBuildsBasicCardinalityAggregation')]
    #[Depends('itCanBeFiltered')]
    #[Depends('itCanBeGlobal')]
    #[Test]
    public function itCanBeGlobalAndFilteredTogether(): void
    {
        $agg = new CardinalityAggregation('unique_colors', 'color');
        $agg = $agg->asGlobal()->query(new BoolQueryFixture('foo'));

        $this->assertEquals([
            'unique_colors' => [
                'global' => (object) [],
                'aggs' => [
                    'unique_colors' => [
                        'filter' => [
                            'foo' => 'fixture_for_bool_query',
                        ],
                        'aggs' => [
                            'unique_colors' => [
                                'cardinality' => [
                                    'field' => 'color',
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
        $agg = new CardinalityAggregation('unique_colors', 'color');
        $this->assertSame('unique_colors', $agg->getName());
    }
}
