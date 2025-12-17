<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use PHPUnit\Framework\Attributes\DependsExternal;
use Bonu\ElasticsearchBuilder\Aggregation\TermsAggregation;
use Bonu\ElasticsearchBuilder\Aggregation\NestedAggregation;
use Bonu\ElasticsearchBuilder\Tests\Fixture\BoolQueryFixture;
use Bonu\ElasticsearchBuilder\Exception\Aggregation\DuplicatedNestedAggregationException;

/**
 * @internal
 */
final class NestedAggregationTest extends TestCase
{
    #[Test]
    public function itBuildsBasicNestedAggregation(): void
    {
        $agg = new NestedAggregation('categories', 'products');

        $this->assertSame([
            'categories' => [
                'nested' => [
                    'path' => 'products',
                ],
                'aggs' => [],
            ],
        ], $agg->toArray());
    }

    #[Depends('itBuildsBasicNestedAggregation')]
    #[DependsExternal(TermsAggregationTest::class, 'itBuildsBasicTermsAggregation')]
    #[Test]
    public function itBuildsWithSimpleSubAggregations(): void
    {
        $agg = new NestedAggregation('categories', 'products')
            ->aggregation(new TermsAggregation('products_stock', 'products.stock'));

        $this->assertSame([
            'categories' => [
                'nested' => [
                    'path' => 'products',
                ],
                'aggs' => [
                    'products_stock' => [
                        'terms' => [
                            'field' => 'products.stock',
                        ],
                    ],
                ],
            ],
        ], $agg->toArray());
    }

    #[Depends('itBuildsBasicNestedAggregation')]
    #[DependsExternal(TermsAggregationTest::class, 'itBuildsBasicTermsAggregation')]
    #[Test]
    public function itBuildsWithMultipleSubAggregations(): void
    {
        $agg = new NestedAggregation('categories', 'products')
            ->aggregation(new TermsAggregation('products_stock', 'products.stock'))
            ->aggregation(new TermsAggregation('products_price', 'products.price'));

        $this->assertSame([
            'categories' => [
                'nested' => [
                    'path' => 'products',
                ],
                'aggs' => [
                    'products_stock' => [
                        'terms' => [
                            'field' => 'products.stock',
                        ],
                    ],
                    'products_price' => [
                        'terms' => [
                            'field' => 'products.price',
                        ],
                    ],
                ],
            ],
        ], $agg->toArray());
    }

    #[Test]
    public function itThrowsExceptionIfTryingToAddSubAggregationWithSameName(): void
    {
        $this->expectException(DuplicatedNestedAggregationException::class);

        new NestedAggregation('categories', 'products')
            ->aggregation(new TermsAggregation('products_stock', 'products.stock'))
            ->aggregation(new TermsAggregation('products_stock', 'products.price'));
    }

    #[Depends('itBuildsBasicNestedAggregation')]
    #[Test]
    public function itCanBeFiltered(): void
    {
        $agg = new NestedAggregation('categories', 'products')
            ->aggregation(new TermsAggregation('products_stock', 'products.stock'))
            ->aggregation(new TermsAggregation('products_price', 'products.price'))
            ->query(new BoolQueryFixture('foo'));

        $this->assertSame([
            'categories' => [
                'filter' => [
                    'foo' => 'fixture_for_bool_query',
                ],
                'aggs' => [
                    'categories' => [
                        'nested' => [
                            'path' => 'products',
                        ],
                        'aggs' => [
                            'products_stock' => [
                                'terms' => [
                                    'field' => 'products.stock',
                                ],
                            ],
                            'products_price' => [
                                'terms' => [
                                    'field' => 'products.price',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], $agg->toArray());
    }

    #[Depends('itBuildsBasicNestedAggregation')]
    #[Test]
    public function itCanBeGlobal(): void
    {
        $agg = new NestedAggregation('categories', 'products')
            ->asGlobal();

        $this->assertEquals([
            'categories' => [
                'global' => (object) [],
                'aggs' => [
                    'categories' => [
                        'nested' => [
                            'path' => 'products',
                        ],
                        'aggs' => [],
                    ],
                ],
            ],
        ], $agg->toArray());
    }
}
