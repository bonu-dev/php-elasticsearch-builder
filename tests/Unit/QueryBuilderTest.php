<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;
use Bonu\ElasticsearchBuilder\QueryBuilder;
use Bonu\ElasticsearchBuilder\Sort\FieldSort;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use PHPUnit\Framework\Attributes\DependsExternal;
use Bonu\ElasticsearchBuilder\Sort\SortDirectionEnum;
use Bonu\ElasticsearchBuilder\Aggregation\TermsAggregation;
use Bonu\ElasticsearchBuilder\Tests\Unit\Sort\FieldSortTest;
use Bonu\ElasticsearchBuilder\Tests\Fixture\BoolQueryFixture;
use Bonu\ElasticsearchBuilder\Tests\Unit\Query\BoolQueryTest;
use Bonu\ElasticsearchBuilder\Exception\Builder\InvalidFromException;
use Bonu\ElasticsearchBuilder\Exception\Builder\InvalidSizeException;
use Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation\TermsAggregationTest;
use Bonu\ElasticsearchBuilder\Exception\Builder\DuplicatedBuilderAggregationException;

/**
 * @internal
 */
final class QueryBuilderTest extends TestCase
{
    #[Test]
    public function itReturnsIndex(): void
    {
        $this->assertSame('foo', new QueryBuilder('foo')->getIndex());
    }

    #[Test]
    public function itReturnsIndexInBody(): void
    {
        $this->assertSame([
            'body' => [],
            'index' => 'foo',
        ], new QueryBuilder('foo')->build());
    }

    #[Depends('itReturnsIndexInBody')]
    #[DependsExternal(BoolQueryTest::class, 'itCorrectlyBuildsArray')]
    #[Test]
    public function itReturnsQueryInBody(): void
    {
        $builder = new QueryBuilder('foo')
            ->query(new BoolQueryFixture('foo'));

        $this->assertSame([
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [['foo' => 'fixture_for_bool_query']],
                        'boost' => 1.0,
                    ],
                ],
            ],
            'index' => 'foo',
        ], $builder->build());
    }

    #[Test]
    public function itThrowsExceptionIfTryingToAddAggregationWithAlreadyExistingName(): void
    {
        $this->expectException(DuplicatedBuilderAggregationException::class);

        new QueryBuilder('foo')
            ->aggregation(new TermsAggregation('tags', 'foo'))
            ->aggregation(new TermsAggregation('tags', 'bar'));
    }

    #[Depends('itReturnsIndexInBody')]
    #[DependsExternal(TermsAggregationTest::class, 'itCanBeGlobalAndFilteredAndSizedTogether')]
    #[Test]
    public function itReturnsAggregationsInBody(): void
    {
        $builder = new QueryBuilder('foo')
            ->aggregation(
                new TermsAggregation('tags', 'category')
                    ->asGlobal()
                    ->query(new BoolQueryFixture('foo'))
            );

        $this->assertEquals([
            'body' => [
                'aggs' => [
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
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'index' => 'foo',
        ], $builder->build());
    }

    #[Depends('itReturnsIndexInBody')]
    #[DependsExternal(TermsAggregationTest::class, 'itBuildsBasicTermsAggregation')]
    #[Test]
    public function itReturnsMultipleAggregationsCorrectly(): void
    {
        $builder = new QueryBuilder('foo')
            ->aggregation(new TermsAggregation('tags', 'category'))
            ->aggregation(new TermsAggregation('tags_2', 'category2'));

        $this->assertSame([
            'body' => [
                'aggs' => [
                    'tags' => [
                        'terms' => [
                            'field' => 'category',
                        ],
                    ],
                    'tags_2' => [
                        'terms' => [
                            'field' => 'category2',
                        ],
                    ],
                ],
            ],
            'index' => 'foo',
        ], $builder->build());
    }

    #[Depends('itReturnsIndexInBody')]
    #[Test]
    public function itReturnsSizeInBody(): void
    {
        $this->assertSame([
            'body' => [
                'size' => 123,
            ],
            'index' => 'foo',
        ], new QueryBuilder('foo')->size(123)->build());
    }

    #[Depends('itReturnsSizeInBody')]
    #[Test]
    public function itThrowsInvalidSizeExceptionIfSizeIsLowerThanOne(): void
    {
        $this->expectException(InvalidSizeException::class);

        new QueryBuilder('foo')->size(0);
    }

    #[Depends('itReturnsIndexInBody')]
    #[Test]
    public function itReturnsFromInBody(): void
    {
        $this->assertSame([
            'body' => [
                'from' => 123,
            ],
            'index' => 'foo',
        ], new QueryBuilder('foo')->from(123)->build());
    }

    #[Depends('itReturnsSizeInBody')]
    #[Test]
    public function itThrowsInvalidFromExceptionIfFromIsLowerThanZero(): void
    {
        $this->expectException(InvalidFromException::class);

        new QueryBuilder('foo')->from(-1);
    }

    #[Depends('itReturnsIndexInBody')]
    #[DependsExternal(FieldSortTest::class, 'itCorrectlyBuildsArray')]
    #[Test]
    public function itReturnsSortsInBody(): void
    {
        $builder = new QueryBuilder('foo')
            ->sort(new FieldSort('foo'))
            ->sort(new FieldSort('bar', SortDirectionEnum::DESC));

        $this->assertSame([
            'body' => [
                'sort' => [
                    [
                        'foo' => [
                            'order' => 'asc',
                        ],
                    ],
                    [
                        'bar' => [
                            'order' => 'desc',
                        ],
                    ],
                ],
            ],
            'index' => 'foo',
        ], $builder->build());
    }
}
