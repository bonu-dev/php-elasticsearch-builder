<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Integration\Aggregation;

use PHPUnit\Framework\Attributes\Test;
use Bonu\ElasticsearchBuilder\QueryBuilder;
use Bonu\ElasticsearchBuilder\Tests\IntegrationTestCase;
use Bonu\ElasticsearchBuilder\Aggregation\CardinalityAggregation;

/**
 * @internal
 */
final class CardinalityAggregationTest extends IntegrationTestCase
{
    #[Test]
    public function itCountsDistinctValues(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->aggregation(new CardinalityAggregation('unique_album_types', 'album_type'))
                ->size(1)
                ->build()
        )->asArray();

        $this->assertArrayHasKey('aggregations', $response);
        $this->assertArrayHasKey('value', $response['aggregations']['unique_album_types']);
        $this->assertGreaterThan(0, $response['aggregations']['unique_album_types']['value']);
    }

    #[Test]
    public function itCountsDistinctValuesWithPrecisionThreshold(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->aggregation(new CardinalityAggregation('unique_albums', 'album_id', 1000))
                ->size(1)
                ->build()
        )->asArray();

        $this->assertArrayHasKey('aggregations', $response);
        $this->assertArrayHasKey('value', $response['aggregations']['unique_albums']);
        $this->assertGreaterThan(0, $response['aggregations']['unique_albums']['value']);
    }
}
