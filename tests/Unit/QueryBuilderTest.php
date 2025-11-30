<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;
use Bonu\ElasticsearchBuilder\QueryBuilder;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use PHPUnit\Framework\Attributes\DependsExternal;
use Bonu\ElasticsearchBuilder\Aggregation\TermsAggregation;
use Bonu\ElasticsearchBuilder\Tests\Fixture\BoolQueryFixture;
use Bonu\ElasticsearchBuilder\Tests\Unit\Query\BoolQueryTest;
use Bonu\ElasticsearchBuilder\Exception\Builder\AggregationAlreadyExistsException;

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

    #[Test]
    #[Depends('itReturnsIndexInBody')]
    #[DependsExternal(BoolQueryTest::class, 'itCorrectlyBuildsArray')]
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function itThrowsExceptionIfTryingToAddAggregationWithAlreadyExistingName(): void
    {
        $this->expectException(AggregationAlreadyExistsException::class);

        new QueryBuilder('foo')
            ->aggregation(new TermsAggregation('tags', 'foo'))
            ->aggregation(new TermsAggregation('tags', 'bar'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function itReturnsAggregationsInBody(): void
    {
        $builder = new QueryBuilder('foo')
            ->aggregation(new TermsAggregation('tags', 'category')
                ->asGlobal()
                ->query(new BoolQueryFixture('foo'))
            );

        $this->assertSame([
            'body' => [
                'aggs' => [
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
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]
                ],
            ],
            'index' => 'foo',
        ], $builder->build());
    }
}
