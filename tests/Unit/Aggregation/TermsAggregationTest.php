<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation;

use PHPUnit\Framework\Attributes\Depends;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DependsExternal;
use Bonu\ElasticsearchBuilder\Aggregation\TermsAggregation;
use Bonu\ElasticsearchBuilder\Tests\Fixture\BoolQueryFixture;
use Bonu\ElasticsearchBuilder\Aggregation\FilterableAggregation;
use Bonu\ElasticsearchBuilder\Aggregation\GlobalizableAggregation;
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

    #[Test]
    #[Depends('itBuildsBasicTermsAggregation')]
    #[DependsExternal(SizeableAggregationTest::class, 'itAddsSizeToAggregation')]
    public function itAddsSizeWhenProvided(): void
    {
        $agg = new TermsAggregation('tags', 'category');
        $agg->size(20);

        $this->assertSame([
            'tags' => [
                'terms' => [
                    'field' => 'category',
                    'size' => 20,
                ],
            ],
        ], $agg->toArray());
    }

    #[Test]
    #[Depends('itBuildsBasicTermsAggregation')]
    #[DependsExternal(GlobalizableAggregationTest::class, 'itAddsGlobalToAggregation')]
    public function itCanBeGlobal(): void
    {
        $agg = new TermsAggregation('tags', 'category');
        $agg->asGlobal();

        $this->assertSame([
            'tags' => [
                'global' => [],
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

    #[Test]
    #[Depends('itBuildsBasicTermsAggregation')]
    #[DependsExternal(FilterableAggregationTest::class, 'itAddsFilterToAggregation')]
    public function itCanBeFiltered(): void
    {
        $agg = new TermsAggregation('tags', 'category');
        $agg->query(new BoolQueryFixture('foo'));

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

    #[Test]
    #[Depends('itBuildsBasicTermsAggregation')]
    #[Depends('itAddsSizeWhenProvided')]
    #[Depends('itCanBeFiltered')]
    #[Depends('itCanBeGlobal')]
    public function itCanBeGlobalAndFilteredAndSizedTogether(): void
    {
        $agg = new TermsAggregation('tags', 'category');
        $agg->size(5)->asGlobal()->query(new BoolQueryFixture('foo'));

        $this->assertSame([
            'tags' => [
                'global' => [],
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
