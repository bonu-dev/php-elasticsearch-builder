<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Query;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use Bonu\ElasticsearchBuilder\Query\NumericRangeQuery;
use Bonu\ElasticsearchBuilder\Query\DatetimeRangeQuery;
use Bonu\ElasticsearchBuilder\Exception\Query\EmptyRangeQueryException;
use Bonu\ElasticsearchBuilder\Exception\Query\InvalidRelationQueryException;

/**
 * @internal
 */
final class NumericRangeQueryTest extends TestCase
{
    #[Test]
    public function itThrowsExceptionIfNoBoundIsSet(): void
    {
        $this->expectException(EmptyRangeQueryException::class);

        new NumericRangeQuery('foo');
    }

    #[Depends('itThrowsExceptionIfNoBoundIsSet')]
    #[Test]
    public function itDoesNotThrowExceptionIfBoundIsSet(): void
    {
        new NumericRangeQuery('foo', 1);

        $this->expectNotToPerformAssertions();
    }

    #[Depends('itDoesNotThrowExceptionIfBoundIsSet')]
    #[Test]
    public function itThrowsExceptionIfInvalidRelationIsProvided(): void
    {
        $this->expectException(InvalidRelationQueryException::class);

        new NumericRangeQuery('bar', lt: 1, relation: 'baz');
    }
}
