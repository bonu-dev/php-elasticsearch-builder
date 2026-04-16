<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Integration\Query;

use PHPUnit\Framework\Attributes\Test;
use Bonu\ElasticsearchBuilder\QueryBuilder;
use Bonu\ElasticsearchBuilder\Query\DatetimeRangeQuery;
use Bonu\ElasticsearchBuilder\Tests\IntegrationTestCase;

/**
 * @internal
 */
final class DatetimeRangeQueryTest extends IntegrationTestCase
{
    #[Test]
    public function itFiltersRecordsByDateRange(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->query(new DatetimeRangeQuery('album_release_date', lte: '2025-12-31', gte: '2025-01-01', format: 'yyyy-MM-dd'))
                ->build()
        )->asArray();

        $this->assertGreaterThan(0, $response['hits']['total']['value']);

        foreach ($response['hits']['hits'] as $hit) {
            $this->assertStringStartsWith('2025-', $hit['_source']['album_release_date']);
        }
    }

    #[Test]
    public function itFiltersRecordsWithLowerBoundOnly(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->query(new DatetimeRangeQuery('album_release_date', gte: '2020-01-01', format: 'yyyy-MM-dd'))
                ->build()
        )->asArray();

        $this->assertGreaterThan(0, $response['hits']['total']['value']);
    }
}
