<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Integration\Aggregation;

use PHPUnit\Framework\Attributes\Test;
use Bonu\ElasticsearchBuilder\QueryBuilder;
use Bonu\ElasticsearchBuilder\Tests\IntegrationTestCase;
use Bonu\ElasticsearchBuilder\Aggregation\DateHistogramAggregation;

/**
 * @internal
 */
final class DateHistogramAggregationTest extends IntegrationTestCase
{
    #[Test]
    public function itCreatesDateHistogramWithCalendarInterval(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->aggregation(new DateHistogramAggregation('releases_by_year', 'album_release_date', calendarInterval: 'year'))
                ->size(1)
                ->build()
        )->asArray();

        $this->assertArrayHasKey('aggregations', $response);
        $this->assertNotEmpty($response['aggregations']['releases_by_year']['buckets']);

        foreach ($response['aggregations']['releases_by_year']['buckets'] as $bucket) {
            $this->assertArrayHasKey('key', $bucket);
            $this->assertArrayHasKey('key_as_string', $bucket);
            $this->assertArrayHasKey('doc_count', $bucket);
        }
    }

    #[Test]
    public function itCreatesDateHistogramWithFixedInterval(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->aggregation(new DateHistogramAggregation('releases_by_quarter', 'album_release_date', fixedInterval: '90d'))
                ->size(1)
                ->build()
        )->asArray();

        $this->assertArrayHasKey('aggregations', $response);
        $this->assertNotEmpty($response['aggregations']['releases_by_quarter']['buckets']);

        foreach ($response['aggregations']['releases_by_quarter']['buckets'] as $bucket) {
            $this->assertArrayHasKey('key', $bucket);
            $this->assertArrayHasKey('doc_count', $bucket);
        }
    }

    #[Test]
    public function itCreatesDateHistogramWithFormatAndMinDocCount(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->aggregation(new DateHistogramAggregation(
                    'releases_by_month',
                    'album_release_date',
                    calendarInterval: 'month',
                    minDocCount: 1,
                    format: 'yyyy-MM',
                ))
                ->size(1)
                ->build()
        )->asArray();

        $this->assertArrayHasKey('aggregations', $response);

        foreach ($response['aggregations']['releases_by_month']['buckets'] as $bucket) {
            $this->assertGreaterThanOrEqual(1, $bucket['doc_count']);
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}$/', $bucket['key_as_string']);
        }
    }
}
