<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Sort;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;
use Bonu\ElasticsearchBuilder\Sort\FieldSort;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use Bonu\ElasticsearchBuilder\Sort\ScoreSort;
use Bonu\ElasticsearchBuilder\Sort\SortDirectionEnum;

/**
 * @internal
 */
final class ScoreSortTest extends TestCase
{
    #[Test]
    public function itCorrectlyBuildsArray(): void
    {
        $sort = new ScoreSort(SortDirectionEnum::DESC);

        $this->assertSame([
            '_score' => [
                'order' => 'desc',
            ],
        ], $sort->toArray());
    }
}
