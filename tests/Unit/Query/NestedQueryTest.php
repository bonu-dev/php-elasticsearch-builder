<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Query;

use PHPUnit\Framework\Attributes\Test;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use Bonu\ElasticsearchBuilder\Query\TermQuery;
use Bonu\ElasticsearchBuilder\Query\NestedQuery;
use PHPUnit\Framework\Attributes\DependsExternal;
use Bonu\ElasticsearchBuilder\Exception\Query\EmptyNestedQueryException;

/**
 * @internal
 */
final class NestedQueryTest extends TestCase
{
    #[Test]
    public function itThrowsExceptionIfNestedQueryIsNotSet(): void
    {
        $this->expectException(EmptyNestedQueryException::class);

        new NestedQuery('foo')->toArray();
    }

    #[DependsExternal(TermQueryTest::class, 'itCorrectlyBuildsArray')]
    #[Test]
    public function itCorrectlyBuildsArray(): void
    {
        $array = new NestedQuery('foo')
            ->query(new TermQuery('bar', 'baz'))
            ->toArray();

        $this->assertSame([
            'nested' => [
                'path' => 'foo',
                'query' => ['term' => ['bar' => ['value' => 'baz', 'boost' => 1.0]]],
            ],
        ], $array);
    }
}
