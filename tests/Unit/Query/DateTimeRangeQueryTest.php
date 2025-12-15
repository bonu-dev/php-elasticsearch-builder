<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Query;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use Bonu\ElasticsearchBuilder\Query\DatetimeRangeQuery;
use Bonu\ElasticsearchBuilder\Exception\Query\EmptyRangeQueryException;
use Bonu\ElasticsearchBuilder\Exception\Query\InvalidRelationQueryException;

/**
 * @internal
 */
final class DateTimeRangeQueryTest extends TestCase
{
    #[Test]
    public function itThrowsExceptionIfNoBoundIsSet(): void
    {
        $this->expectException(EmptyRangeQueryException::class);

        new DatetimeRangeQuery('foo');
    }

    #[Depends('itThrowsExceptionIfNoBoundIsSet')]
    #[Test]
    public function itDoesNotThrowExceptionIfBoundIsSet(): void
    {
        new DatetimeRangeQuery('foo', '2021-01-01');

        $this->expectNotToPerformAssertions();
    }

    #[Depends('itDoesNotThrowExceptionIfBoundIsSet')]
    #[Test]
    public function itThrowsExceptionIfInvalidRelationIsProvided(): void
    {
        $this->expectException(InvalidRelationQueryException::class);

        new DatetimeRangeQuery('bar', lt: '2021-01-01', relation: 'baz');
    }
}
