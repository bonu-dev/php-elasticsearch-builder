<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Query;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Bonu\ElasticsearchBuilder\Query\NumericRangeQuery;
use Bonu\ElasticsearchBuilder\Query\DatetimeRangeQuery;
use Bonu\ElasticsearchBuilder\Tests\Fixture\UniversalRangeQueryFixture;
use Bonu\ElasticsearchBuilder\Exception\Query\InvalidRelationQueryException;

use const PHP_FLOAT_EPSILON;

/**
 * @internal
 */
#[CoversClass(NumericRangeQuery::class)]
#[CoversClass(DatetimeRangeQuery::class)]
final class RangeQueryTest extends TestCase
{
    #[Test]
    public function itThrowsExceptionIfInvalidRelationIsProvided(): void
    {
        $this->expectException(InvalidRelationQueryException::class);

        new UniversalRangeQueryFixture('bar', relation: 'foo');
    }

    #[Test]
    public function itCorrectlyBuildsArray(): void
    {
        $query = new UniversalRangeQueryFixture(
            field: 'foo',
            lt: 1,
            lte: 2,
            gt: 3,
            gte: 4,
            format: 'yyyy-MM-dd',
            relation: UniversalRangeQueryFixture::RELATION_WITHIN,
            timeZone: 'Europe/Prague',
        );

        $this->assertSame([
            'range' => [
                'foo' => [
                    'lt' => 1,
                    'lte' => 2,
                    'gt' => 3,
                    'gte' => 4,
                    'format' => 'yyyy-MM-dd',
                    'relation' => 'WITHIN',
                    'time_zone' => 'Europe/Prague',
                    'boost' => 1.0,
                ],
            ],
        ], $query->toArray());
    }

    #[Depends('itCorrectlyBuildsArray')]
    #[Test]
    public function itCorrectlySetsBoost(): void
    {
        $array = new UniversalRangeQueryFixture('foo', 'bar')->boost(10.0)->toArray();

        $this->assertEqualsWithDelta(10.0, $array['range']['foo']['boost'], PHP_FLOAT_EPSILON);
    }

    #[Depends('itCorrectlyBuildsArray')]
    #[Test]
    public function itDoesNotIncludeEmptyValuesInFinalArray(): void
    {
        $this->assertSame([
            'range' => [
                'foo' => [
                    'lt' => 1,
                    'boost' => 1.0,
                ],
            ],
        ], new UniversalRangeQueryFixture(
            field: 'foo',
            lt: 1,
        )->toArray());

        $this->assertSame([
            'range' => [
                'foo' => [
                    'gte' => 1,
                    'boost' => 1.0,
                ],
            ],
        ], new UniversalRangeQueryFixture(
            field: 'foo',
            gte: 1,
        )->toArray());

        $this->assertSame([
            'range' => [
                'foo' => [
                    'gt' => 0,
                    'boost' => 1.0,
                ],
            ],
        ], new UniversalRangeQueryFixture(
            field: 'foo',
            gt: 0,
        )->toArray());
    }
}
