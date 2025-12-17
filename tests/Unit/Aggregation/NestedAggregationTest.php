<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation;

use PHPUnit\Framework\Attributes\Test;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use PHPUnit\Framework\Attributes\DependsExternal;
use Bonu\ElasticsearchBuilder\Aggregation\TermsAggregation;
use Bonu\ElasticsearchBuilder\Aggregation\NestedAggregation;

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
            ],
        ], $agg->toArray());
    }

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
}
