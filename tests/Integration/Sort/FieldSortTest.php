<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Integration\Sort;

use PHPUnit\Framework\Attributes\Test;
use Bonu\ElasticsearchBuilder\QueryBuilder;
use Bonu\ElasticsearchBuilder\Sort\FieldSort;
use Bonu\ElasticsearchBuilder\Sort\SortDirectionEnum;
use Bonu\ElasticsearchBuilder\Tests\IntegrationTestCase;

use function count;

/**
 * @internal
 */
final class FieldSortTest extends IntegrationTestCase
{
    #[Test]
    public function itSortsResultsByFieldAscending(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->sort(new FieldSort('track_popularity', SortDirectionEnum::ASC))
                ->size(5)
                ->build()
        )->asArray();

        $hits = $response['hits']['hits'];
        $this->assertCount(5, $hits);
        $counter = count($hits);

        for ($i = 1; $i < $counter; $i++) {
            $this->assertGreaterThanOrEqual(
                $hits[$i - 1]['_source']['track_popularity'],
                $hits[$i]['_source']['track_popularity'],
            );
        }
    }

    #[Test]
    public function itSortsResultsByFieldDescending(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->sort(new FieldSort('track_popularity', SortDirectionEnum::DESC))
                ->size(5)
                ->build()
        )->asArray();

        $hits = $response['hits']['hits'];
        $this->assertCount(5, $hits);
        $counter = count($hits);

        for ($i = 1; $i < $counter; $i++) {
            $this->assertLessThanOrEqual(
                $hits[$i - 1]['_source']['track_popularity'],
                $hits[$i]['_source']['track_popularity'],
            );
        }
    }
}
