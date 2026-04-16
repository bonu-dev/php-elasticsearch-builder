<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Integration\Query;

use PHPUnit\Framework\Attributes\Test;
use Bonu\ElasticsearchBuilder\QueryBuilder;
use Bonu\ElasticsearchBuilder\Query\NumericRangeQuery;
use Bonu\ElasticsearchBuilder\Tests\IntegrationTestCase;

/**
 * @internal
 */
final class NumericRangeQueryTest extends IntegrationTestCase
{
    #[Test]
    public function itFiltersRecordsByNumericRange(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->query(new NumericRangeQuery('track_popularity', gte: 90))
                ->build()
        )->asArray();

        $this->assertGreaterThan(0, $response['hits']['total']['value']);

        foreach ($response['hits']['hits'] as $hit) {
            $this->assertGreaterThanOrEqual(90, $hit['_source']['track_popularity']);
        }
    }

    #[Test]
    public function itFiltersRecordsWithBothBounds(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->query(new NumericRangeQuery('track_popularity', lte: 60, gte: 50))
                ->build()
        )->asArray();

        $this->assertGreaterThan(0, $response['hits']['total']['value']);

        foreach ($response['hits']['hits'] as $hit) {
            $this->assertGreaterThanOrEqual(50, $hit['_source']['track_popularity']);
            $this->assertLessThanOrEqual(60, $hit['_source']['track_popularity']);
        }
    }
}
