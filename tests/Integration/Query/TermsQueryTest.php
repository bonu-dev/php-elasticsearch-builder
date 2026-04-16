<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Integration\Query;

use PHPUnit\Framework\Attributes\Test;
use Bonu\ElasticsearchBuilder\QueryBuilder;
use Bonu\ElasticsearchBuilder\Query\TermsQuery;
use Bonu\ElasticsearchBuilder\Tests\IntegrationTestCase;

/**
 * @internal
 */
final class TermsQueryTest extends IntegrationTestCase
{
    #[Test]
    public function itFiltersRecordsByMultipleTerms(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->query(new TermsQuery('track_id', ['0L0LgwFZ7UtBnRNQvSBty6', 'nonexistent_track_id']))
                ->build()
        )->asArray();

        $this->assertSame(1, $response['hits']['total']['value']);
        $this->assertSame('0L0LgwFZ7UtBnRNQvSBty6', $response['hits']['hits'][0]['_source']['track_id']);
    }

    #[Test]
    public function itFiltersRecordsByKnownKeywordField(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->query(new TermsQuery('album_type', ['album', 'single']))
                ->build()
        )->asArray();

        $this->assertGreaterThan(0, $response['hits']['total']['value']);
    }
}
