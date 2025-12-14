<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use PHPUnit\Framework\Attributes\DependsExternal;
use Bonu\ElasticsearchBuilder\Aggregation\TermsAggregation;
use Bonu\ElasticsearchBuilder\Tests\Fixture\BoolQueryFixture;
use Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation\Trait\SizeableAggregationTest;
use Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation\Trait\FilterableAggregationTest;
use Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation\Trait\GlobalizableAggregationTest;

/**
 * @internal
 */
final class TermsAggregationTest extends TestCase
{
    #[Test]
    public function itBuildsBasicTermsAggregation(): void
    {
        $agg = new TermsAggregation('tags', 'category');

        $this->assertSame([
            'tags' => [
                'terms' => [
                    'field' => 'category',
                ],
            ],
        ], $agg->toArray());
    }

    #[Depends('itBuildsBasicTermsAggregation')]
    #[DependsExternal(SizeableAggregationTest::class, 'itAddsSizeToAggregation')]
    #[Test]
    public function itAddsSizeWhenProvided(): void
    {
        $agg = new TermsAggregation('tags', 'category');
        $agg = $agg->size(20);

        $this->assertSame([
            'tags' => [
                'terms' => [
                    'field' => 'category',
                    'size' => 20,
                ],
            ],
        ], $agg->toArray());
    }

    #[Depends('itBuildsBasicTermsAggregation')]
    #[DependsExternal(GlobalizableAggregationTest::class, 'itAddsGlobalToAggregation')]
    #[Test]
    public function itCanBeGlobal(): void
    {
        $agg = new TermsAggregation('tags', 'category');
        $agg = $agg->asGlobal();

        $this->assertEquals([
            'tags' => [
                'global' => (object) [],
                'aggs' => [
                    'tags' => [
                        'terms' => [
                            'field' => 'category',
                        ],
                    ],
                ],
            ],
        ], $agg->toArray());
    }

    #[Depends('itBuildsBasicTermsAggregation')]
    #[DependsExternal(FilterableAggregationTest::class, 'itAddsFilterToAggregation')]
    #[Test]
    public function itCanBeFiltered(): void
    {
        $agg = new TermsAggregation('tags', 'category');
        $agg = $agg->query(new BoolQueryFixture('foo'));

        $this->assertSame([
            'tags' => [
                'filter' => [
                    'foo' => 'fixture_for_bool_query',
                ],
                'aggs' => [
                    'tags' => [
                        'terms' => [
                            'field' => 'category',
                        ],
                    ],
                ],
            ],
        ], $agg->toArray());
    }

    #[Depends('itBuildsBasicTermsAggregation')]
    #[Depends('itAddsSizeWhenProvided')]
    #[Depends('itCanBeFiltered')]
    #[Depends('itCanBeGlobal')]
    #[Test]
    public function itCanBeGlobalAndFilteredAndSizedTogether(): void
    {
        $agg = new TermsAggregation('tags', 'category');
        $agg = $agg->size(5)->asGlobal()->query(new BoolQueryFixture('foo'));

        $this->assertEquals([
            'tags' => [
                'global' => (object) [],
                'aggs' => [
                    'tags' => [
                        'filter' => [
                            'foo' => 'fixture_for_bool_query',
                        ],
                        'aggs' => [
                            'tags' => [
                                'terms' => [
                                    'field' => 'category',
                                    'size' => 5,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], $agg->toArray());
    }
}
