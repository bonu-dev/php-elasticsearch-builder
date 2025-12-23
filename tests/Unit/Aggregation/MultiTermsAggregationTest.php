<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use PHPUnit\Framework\Attributes\DependsExternal;
use Bonu\ElasticsearchBuilder\Tests\Fixture\BoolQueryFixture;
use Bonu\ElasticsearchBuilder\Aggregation\MultiTermsAggregation;
use Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation\Trait\SizeableAggregationTest;
use Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation\Trait\FilterableAggregationTest;
use Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation\Trait\GlobalizableAggregationTest;
use Bonu\ElasticsearchBuilder\Exception\Aggregation\NotEnoughFieldsAggregationException;

/**
 * @internal
 */
final class MultiTermsAggregationTest extends TestCase
{
    #[Test]
    public function itThrowsExceptionWhenEnoughFieldsWereNotProvided(): void
    {
        $this->expectException(NotEnoughFieldsAggregationException::class);

        new MultiTermsAggregation('tags', ['foo']);
    }

    #[Test]
    public function itBuildsBasicMultiTermsAggregation(): void
    {
        $agg = new MultiTermsAggregation('tags', ['product', 'category']);

        $this->assertSame([
            'tags' => [
                'multi_terms' => [
                    'terms' => [
                        ['field' => 'product'],
                        ['field' => 'category'],
                    ],
                ],
            ],
        ], $agg->toArray());
    }

    #[Depends('itBuildsBasicMultiTermsAggregation')]
    #[DependsExternal(SizeableAggregationTest::class, 'itAddsSizeToAggregation')]
    #[Test]
    public function itAddsSizeWhenProvided(): void
    {
        $agg = new MultiTermsAggregation('tags', ['product', 'category']);
        $agg = $agg->size(20);

        $this->assertSame([
            'tags' => [
                'multi_terms' => [
                    'terms' => [
                        ['field' => 'product'],
                        ['field' => 'category'],
                    ],
                    'size' => 20,
                ],
            ],
        ], $agg->toArray());
    }

    #[Depends('itBuildsBasicMultiTermsAggregation')]
    #[DependsExternal(GlobalizableAggregationTest::class, 'itAddsGlobalToAggregation')]
    #[Test]
    public function itCanBeGlobal(): void
    {
        $agg = new MultiTermsAggregation('tags', ['product', 'category']);
        $agg = $agg->asGlobal();

        $this->assertEquals([
            'tags' => [
                'global' => (object) [],
                'aggs' => [
                    'tags' => [
                        'multi_terms' => [
                            'terms' => [
                                ['field' => 'product'],
                                ['field' => 'category'],
                            ],
                        ],
                    ],
                ],
            ],
        ], $agg->toArray());
    }

    #[Depends('itBuildsBasicMultiTermsAggregation')]
    #[DependsExternal(FilterableAggregationTest::class, 'itAddsFilterToAggregation')]
    #[Test]
    public function itCanBeFiltered(): void
    {
        $agg = new MultiTermsAggregation('tags', ['product', 'category']);
        $agg = $agg->query(new BoolQueryFixture('foo'));

        $this->assertSame([
            'tags' => [
                'filter' => [
                    'foo' => 'fixture_for_bool_query',
                ],
                'aggs' => [
                    'tags' => [
                        'multi_terms' => [
                            'terms' => [
                                ['field' => 'product'],
                                ['field' => 'category'],
                            ],
                        ],
                    ],
                ],
            ],
        ], $agg->toArray());
    }

    #[Depends('itBuildsBasicMultiTermsAggregation')]
    #[Depends('itAddsSizeWhenProvided')]
    #[Depends('itCanBeFiltered')]
    #[Depends('itCanBeGlobal')]
    #[Test]
    public function itCanBeGlobalAndFilteredAndSizedTogether(): void
    {
        $agg = new MultiTermsAggregation('tags', ['product', 'category']);
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
                                'multi_terms' => [
                                    'terms' => [
                                        ['field' => 'product'],
                                        ['field' => 'category'],
                                    ],
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
