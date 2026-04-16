<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Integration\Aggregation;

use PHPUnit\Framework\Attributes\Test;
use Bonu\ElasticsearchBuilder\QueryBuilder;
use Bonu\ElasticsearchBuilder\Tests\IntegrationTestCase;
use Bonu\ElasticsearchBuilder\Aggregation\StatsAggregation;

/**
 * @internal
 */
final class StatsAggregationTest extends IntegrationTestCase
{
    #[Test]
    public function itCalculatesFieldStatistics(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->aggregation(new StatsAggregation('popularity_stats', 'track_popularity'))
                ->size(1)
                ->build()
        )->asArray();

        $this->assertArrayHasKey('aggregations', $response);

        $stats = $response['aggregations']['popularity_stats'];
        $this->assertArrayHasKey('min', $stats);
        $this->assertArrayHasKey('max', $stats);
        $this->assertArrayHasKey('avg', $stats);
        $this->assertArrayHasKey('sum', $stats);
        $this->assertArrayHasKey('count', $stats);
        $this->assertGreaterThanOrEqual(0, $stats['min']);
        $this->assertLessThanOrEqual(100, $stats['max']);
        $this->assertGreaterThan(0, $stats['count']);
    }
}
