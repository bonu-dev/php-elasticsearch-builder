<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Integration;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;
use Bonu\ElasticsearchBuilder\QueryBuilder;
use Bonu\ElasticsearchBuilder\Tests\IntegrationTestCase;

/**
 * @internal
 */
final class QueryBuilderTest extends IntegrationTestCase
{
    #[Test]
    public function itWorksWithBaseQuery(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)->build()
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function itWorksWithSize(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)->size(1)->build()
        )->asArray();
        $this->assertCount(1, $response['hits']['hits']);
    }

    #[Depends('itWorksWithSize')]
    #[Test]
    public function itWorksWithFrom(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)->size(2)->from(0)->build()
        )->asArray();
        $this->assertCount(2, $response['hits']['hits']);

        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)->size(2)->from(1)->build()
        )->asArray();
        $this->assertCount(2, $response['hits']['hits']);
    }
}
