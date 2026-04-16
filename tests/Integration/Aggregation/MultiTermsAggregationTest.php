<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Integration\Aggregation;

use PHPUnit\Framework\Attributes\Test;
use Bonu\ElasticsearchBuilder\QueryBuilder;
use Bonu\ElasticsearchBuilder\Tests\IntegrationTestCase;
use Bonu\ElasticsearchBuilder\Aggregation\MultiTermsAggregation;

/**
 * @internal
 */
final class MultiTermsAggregationTest extends IntegrationTestCase
{
    #[Test]
    public function itAggregatesByMultipleFields(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->aggregation(new MultiTermsAggregation('by_type_and_explicit', ['album_type', 'explicit']))
                ->size(1)
                ->build()
        )->asArray();

        $this->assertArrayHasKey('aggregations', $response);
        $this->assertNotEmpty($response['aggregations']['by_type_and_explicit']['buckets']);

        foreach ($response['aggregations']['by_type_and_explicit']['buckets'] as $bucket) {
            $this->assertArrayHasKey('key', $bucket);
            $this->assertArrayHasKey('doc_count', $bucket);
            $this->assertCount(2, $bucket['key']);
            $this->assertGreaterThan(0, $bucket['doc_count']);
        }
    }
}
