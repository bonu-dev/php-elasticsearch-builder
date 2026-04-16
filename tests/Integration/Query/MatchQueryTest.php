<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Integration\Query;

use PHPUnit\Framework\Attributes\Test;
use Bonu\ElasticsearchBuilder\QueryBuilder;
use Bonu\ElasticsearchBuilder\Query\MatchQuery;
use Bonu\ElasticsearchBuilder\Tests\IntegrationTestCase;

/**
 * @internal
 */
final class MatchQueryTest extends IntegrationTestCase
{
    #[Test]
    public function itMatchesRecordsByFullText(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->query(new MatchQuery('artist_name', 'Diplo'))
                ->build()
        )->asArray();

        $this->assertGreaterThan(0, $response['hits']['total']['value']);
    }

    #[Test]
    public function itMatchesRecordsWithAndOperator(): void
    {
        $orResponse = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->query(new MatchQuery('track_name', 'Trippy Mane'))
                ->build()
        )->asArray();

        $andResponse = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->query(new MatchQuery('track_name', 'Trippy Mane', MatchQuery::OPERATOR_AND))
                ->build()
        )->asArray();

        $this->assertGreaterThan(0, $andResponse['hits']['total']['value']);
        $this->assertGreaterThanOrEqual($andResponse['hits']['total']['value'], $orResponse['hits']['total']['value']);
    }
}
