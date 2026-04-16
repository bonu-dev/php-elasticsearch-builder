<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Integration\Aggregation;

use PHPUnit\Framework\Attributes\Test;
use Bonu\ElasticsearchBuilder\QueryBuilder;
use Bonu\ElasticsearchBuilder\Tests\IntegrationTestCase;
use Bonu\ElasticsearchBuilder\Aggregation\HistogramAggregation;

/**
 * @internal
 */
final class HistogramAggregationTest extends IntegrationTestCase
{
    #[Test]
    public function itCreatesHistogramBuckets(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->aggregation(new HistogramAggregation('popularity_histogram', 'track_popularity', 10))
                ->size(1)
                ->build()
        )->asArray();

        $this->assertArrayHasKey('aggregations', $response);
        $this->assertNotEmpty($response['aggregations']['popularity_histogram']['buckets']);

        foreach ($response['aggregations']['popularity_histogram']['buckets'] as $bucket) {
            $this->assertArrayHasKey('key', $bucket);
            $this->assertArrayHasKey('doc_count', $bucket);
        }
    }

    #[Test]
    public function itCreatesHistogramWithMinDocCount(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->aggregation(new HistogramAggregation('popularity_histogram', 'track_popularity', 10, 1))
                ->size(1)
                ->build()
        )->asArray();

        foreach ($response['aggregations']['popularity_histogram']['buckets'] as $bucket) {
            $this->assertGreaterThanOrEqual(1, $bucket['doc_count']);
        }
    }
}
