<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Query;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use Bonu\ElasticsearchBuilder\Query\BoolQuery;
use Bonu\ElasticsearchBuilder\Tests\Fixture\BoolQueryFixture;
use Bonu\ElasticsearchBuilder\Exception\Query\EmptyBoolQueryException;

final class BoolQueryTest extends TestCase
{
    #[Test]
    public function itThrowsExceptionIfBoolQueryIsEmpty(): void
    {
        $this->expectException(EmptyBoolQueryException::class);

        new BoolQuery()->toArray();
    }

    #[Test]
    public function itCorrectlyBuildsArray(): void
    {
        $query = new BoolQuery()
            ->must(new BoolQueryFixture('foo'))
            ->filter(new BoolQueryFixture('bar'))
            ->should(new BoolQueryFixture('baz'))
            ->mustNot(new BoolQueryFixture('doe'));

        $this->assertSame([
            'bool' => [
                'must' => [
                    ['foo' => 'fixture_for_bool_query']
                ],
                'filter' => [
                    ['bar' => 'fixture_for_bool_query']
                ],
                'should' => [
                    ['baz' => 'fixture_for_bool_query']
                ],
                'must_not' => [
                    ['doe' => 'fixture_for_bool_query']
                ],
                'boost' => 1.0,
            ],
        ], $query->toArray());
    }

    #[Test]
    #[Depends('itCorrectlyBuildsArray')]
    public function itCorrectlySetsBoost(): void
    {
        $query = new BoolQuery()->must(new BoolQueryFixture('foo'))->boost(10.0);

        $this->assertSame([
            'bool' => [
                'must' => [
                    ['foo' => 'fixture_for_bool_query']
                ],
                'boost' => 10.0,
            ],
        ], $query->toArray());
    }

    #[Test]
    #[Depends('itCorrectlyBuildsArray')]
    public function itMergesSameNestedBoolQueries(): void
    {
        $query = new BoolQuery()
            ->must(new BoolQueryFixture('foo'))
            ->must(
                new BoolQuery()
                ->must(new BoolQueryFixture('foo_nested'))
            )
            ->filter(new BoolQueryFixture('bar'))
            ->filter(
                new BoolQuery()
                ->filter(new BoolQueryFixture('bar_nested'))
            )
            ->should(new BoolQueryFixture('baz'))
            ->should(
                new BoolQuery()
                ->should(new BoolQueryFixture('baz_nested'))
            )
            ->mustNot(new BoolQueryFixture('doe'))
            ->mustNot(
                new BoolQuery()
                ->mustNot(new BoolQueryFixture('doe_nested'))
            );

        $this->assertSame([
            'bool' => [
                'must' => [
                    ['foo' => 'fixture_for_bool_query'],
                    ['foo_nested' => 'fixture_for_bool_query'],
                ],
                'filter' => [
                    ['bar' => 'fixture_for_bool_query'],
                    ['bar_nested' => 'fixture_for_bool_query'],
                ],
                'should' => [
                    ['baz' => 'fixture_for_bool_query'],
                    ['baz_nested' => 'fixture_for_bool_query'],
                ],
                'must_not' => [
                    ['doe' => 'fixture_for_bool_query'],
                    ['doe_nested' => 'fixture_for_bool_query'],
                ],
                'boost' => 1.0,
            ],
        ], $query->toArray());
    }

    #[Test]
    public function itCorrectlyBuildsNestedBoolQueries(): void
    {
        $query = new BoolQuery()
            ->must(new BoolQueryFixture('foo'))
            ->must(
                new BoolQuery()
                ->filter(new BoolQueryFixture('foo_nested'))
            )
            ->filter(new BoolQueryFixture('bar'))
            ->filter(
                new BoolQuery()
                ->must(new BoolQueryFixture('bar_nested'))
            )
            ->should(new BoolQueryFixture('baz'))
            ->should(
                new BoolQuery()
                ->must(new BoolQueryFixture('baz_nested'))
            )
            ->mustNot(new BoolQueryFixture('doe'))
            ->mustNot(
                new BoolQuery()
                ->must(new BoolQueryFixture('doe_nested'))
            );

        $this->assertSame([
            'bool' => [
                'must' => [
                    ['foo' => 'fixture_for_bool_query'],
                    ['bool' => [
                        'filter' => [['foo_nested' => 'fixture_for_bool_query']],
                        'boost' => 1.0,
                    ]]
                ],
                'filter' => [
                    ['bar' => 'fixture_for_bool_query'],
                    ['bool' => [
                        'must' => [['bar_nested' => 'fixture_for_bool_query']],
                        'boost' => 1.0,
                    ]]
                ],
                'should' => [
                    ['baz' => 'fixture_for_bool_query'],
                    ['bool' => [
                        'must' => [['baz_nested' => 'fixture_for_bool_query']],
                        'boost' => 1.0,
                    ]]
                ],
                'must_not' => [
                    ['doe' => 'fixture_for_bool_query'],
                    ['bool' => [
                        'must' => [['doe_nested' => 'fixture_for_bool_query']],
                        'boost' => 1.0,
                    ]]
                ],
                'boost' => 1.0,
            ],
        ], $query->toArray());
    }
}
