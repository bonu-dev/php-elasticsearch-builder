<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Integration\Aggregation;

use PHPUnit\Framework\Attributes\Test;
use Bonu\ElasticsearchBuilder\QueryBuilder;
use Bonu\ElasticsearchBuilder\Query\TermQuery;
use Bonu\ElasticsearchBuilder\Tests\IntegrationTestCase;
use Bonu\ElasticsearchBuilder\Aggregation\StatsAggregation;
use Bonu\ElasticsearchBuilder\Aggregation\TermsAggregation;
use Bonu\ElasticsearchBuilder\Aggregation\ContainerAggregation;

/**
 * @internal
 */
final class ContainerAggregationTest extends IntegrationTestCase
{
    #[Test]
    public function itGroupsSubAggregationsWithFilter(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->aggregation(
                    new ContainerAggregation('filtered_container')
                        ->query(new TermQuery('album_type', 'album'))
                        ->aggregation(new TermsAggregation('by_explicit', 'explicit'))
                )
                ->size(1)
                ->build()
        )->asArray();

        $this->assertArrayHasKey('aggregations', $response);
        $this->assertArrayHasKey('filtered_container', $response['aggregations']);
        $this->assertArrayHasKey('doc_count', $response['aggregations']['filtered_container']);
        $this->assertGreaterThan(0, $response['aggregations']['filtered_container']['doc_count']);
        $this->assertNotEmpty($response['aggregations']['filtered_container']['by_explicit']['buckets']);
    }

    #[Test]
    public function itGroupsSubAggregationsAsGlobal(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->query(new TermQuery('album_type', 'single'))
                ->aggregation(
                    new ContainerAggregation('global_container')
                        ->asGlobal()
                        ->aggregation(new StatsAggregation('popularity_stats', 'track_popularity'))
                )
                ->size(1)
                ->build()
        )->asArray();

        $this->assertArrayHasKey('aggregations', $response);
        $this->assertArrayHasKey('global_container', $response['aggregations']);
        $this->assertArrayHasKey('doc_count', $response['aggregations']['global_container']);
        $this->assertGreaterThan(
            $response['hits']['total']['value'],
            $response['aggregations']['global_container']['doc_count'],
        );
        $this->assertArrayHasKey('popularity_stats', $response['aggregations']['global_container']);
    }
}
