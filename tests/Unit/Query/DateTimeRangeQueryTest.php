<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Query;

use PHPUnit\Framework\Attributes\Test;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use Bonu\ElasticsearchBuilder\Query\DatetimeRangeQuery;
use Bonu\ElasticsearchBuilder\Exception\Query\InvalidRelationQueryException;

/**
 * @internal
 */
final class DateTimeRangeQueryTest extends TestCase
{
    #[Test]
    public function itThrowsExceptionIfInvalidRelationIsProvided(): void
    {
        $this->expectException(InvalidRelationQueryException::class);

        new DatetimeRangeQuery('bar', relation: 'baz');
    }
}
