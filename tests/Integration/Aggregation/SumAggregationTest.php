<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Integration\Aggregation;

use PHPUnit\Framework\Attributes\Test;
use Bonu\ElasticsearchBuilder\QueryBuilder;
use Bonu\ElasticsearchBuilder\Tests\IntegrationTestCase;
use Bonu\ElasticsearchBuilder\Aggregation\SumAggregation;

/**
 * @internal
 */
final class SumAggregationTest extends IntegrationTestCase
{
    #[Test]
    public function itCalculatesSum(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->aggregation(new SumAggregation('total_popularity', 'track_popularity'))
                ->size(1)
                ->build()
        )->asArray();

        $this->assertArrayHasKey('aggregations', $response);
        $this->assertArrayHasKey('value', $response['aggregations']['total_popularity']);
        $this->assertGreaterThan(0, $response['aggregations']['total_popularity']['value']);
    }

    #[Test]
    public function itCalculatesSumOfFloatField(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->aggregation(new SumAggregation('total_duration', 'track_duration_min'))
                ->size(1)
                ->build()
        )->asArray();

        $this->assertArrayHasKey('aggregations', $response);
        $this->assertIsFloat($response['aggregations']['total_duration']['value']);
        $this->assertGreaterThan(0, $response['aggregations']['total_duration']['value']);
    }
}
