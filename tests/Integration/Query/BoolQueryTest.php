<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Integration\Query;

use PHPUnit\Framework\Attributes\Test;
use Bonu\ElasticsearchBuilder\QueryBuilder;
use Bonu\ElasticsearchBuilder\Query\BoolQuery;
use Bonu\ElasticsearchBuilder\Query\TermQuery;
use Bonu\ElasticsearchBuilder\Query\MatchQuery;
use Bonu\ElasticsearchBuilder\Tests\IntegrationTestCase;

/**
 * @internal
 */
final class BoolQueryTest extends IntegrationTestCase
{
    #[Test]
    public function itCombinesFilterAndMustNot(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->query(
                    new BoolQuery()
                        ->filter(new TermQuery('album_type', 'album'))
                        ->mustNot(new TermQuery('explicit', true))
                )
                ->build()
        )->asArray();

        $this->assertGreaterThan(0, $response['hits']['total']['value']);

        foreach ($response['hits']['hits'] as $hit) {
            $this->assertSame('album', $hit['_source']['album_type']);
            $this->assertFalse($hit['_source']['explicit']);
        }
    }

    #[Test]
    public function itCombinesFilterAndShould(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->query(
                    new BoolQuery()
                        ->filter(new TermQuery('album_type', 'single'))
                        ->should(new MatchQuery('artist_name', 'Diplo'))
                )
                ->build()
        )->asArray();

        $this->assertGreaterThan(0, $response['hits']['total']['value']);

        foreach ($response['hits']['hits'] as $hit) {
            $this->assertSame('single', $hit['_source']['album_type']);
        }
    }
}
