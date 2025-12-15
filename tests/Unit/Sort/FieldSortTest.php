<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Sort;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;
use Bonu\ElasticsearchBuilder\Sort\FieldSort;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use Bonu\ElasticsearchBuilder\Sort\SortDirectionEnum;

/**
 * @internal
 */
final class FieldSortTest extends TestCase
{
    #[Test]
    public function itCorrectlyBuildsArray(): void
    {
        $sort = new FieldSort('foo', SortDirectionEnum::DESC, 'foo_bar');

        $this->assertSame([
            'foo' => [
                'order' => 'desc',
                'format' => 'foo_bar',
            ],
        ], $sort->toArray());
    }

    #[Depends('itCorrectlyBuildsArray')]
    #[Test]
    public function itDoesNotIncludeFormatInArrayIfItIsNull(): void
    {
        $sort = new FieldSort('foo', SortDirectionEnum::DESC);

        $this->assertArrayNotHasKey('format', $sort->toArray()['foo']);
    }
}
