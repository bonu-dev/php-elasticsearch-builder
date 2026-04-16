<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Integration\Aggregation;

use PHPUnit\Framework\Attributes\Test;
use Bonu\ElasticsearchBuilder\QueryBuilder;
use Bonu\ElasticsearchBuilder\Tests\IntegrationTestCase;
use Bonu\ElasticsearchBuilder\Aggregation\TermsAggregation;

/**
 * @internal
 */
final class TermsAggregationTest extends IntegrationTestCase
{
    #[Test]
    public function itAggregatesTermsBuckets(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->aggregation(new TermsAggregation('by_album_type', 'album_type'))
                ->size(1)
                ->build()
        )->asArray();

        $this->assertArrayHasKey('aggregations', $response);
        $this->assertNotEmpty($response['aggregations']['by_album_type']['buckets']);

        foreach ($response['aggregations']['by_album_type']['buckets'] as $bucket) {
            $this->assertArrayHasKey('key', $bucket);
            $this->assertArrayHasKey('doc_count', $bucket);
            $this->assertGreaterThan(0, $bucket['doc_count']);
        }
    }

    #[Test]
    public function itAggregatesTermsWithSize(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->aggregation(new TermsAggregation('by_album_type', 'album_type')->size(1))
                ->size(1)
                ->build()
        )->asArray();

        $this->assertCount(1, $response['aggregations']['by_album_type']['buckets']);
    }
}
