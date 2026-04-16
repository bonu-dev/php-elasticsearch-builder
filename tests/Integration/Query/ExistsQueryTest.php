<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Integration\Query;

use PHPUnit\Framework\Attributes\Test;
use Bonu\ElasticsearchBuilder\QueryBuilder;
use Bonu\ElasticsearchBuilder\Query\ExistsQuery;
use Bonu\ElasticsearchBuilder\Tests\IntegrationTestCase;

/**
 * @internal
 */
final class ExistsQueryTest extends IntegrationTestCase
{
    #[Test]
    public function itFiltersDocumentsWhereFieldExists(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->query(new ExistsQuery('track_id'))
                ->build()
        )->asArray();

        $this->assertGreaterThan(0, $response['hits']['total']['value']);
    }
}
