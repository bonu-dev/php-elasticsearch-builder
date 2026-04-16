<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Integration\Query;

use PHPUnit\Framework\Attributes\Test;
use Bonu\ElasticsearchBuilder\QueryBuilder;
use Bonu\ElasticsearchBuilder\Query\MatchPhraseQuery;
use Bonu\ElasticsearchBuilder\Tests\IntegrationTestCase;

/**
 * @internal
 */
final class MatchPhraseQueryTest extends IntegrationTestCase
{
    #[Test]
    public function itMatchesExactPhrase(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->query(new MatchPhraseQuery('track_name', 'Trippy Mane'))
                ->build()
        )->asArray();

        $this->assertGreaterThan(0, $response['hits']['total']['value']);
    }

    #[Test]
    public function itMatchesPhraseWithSlop(): void
    {
        $exactResponse = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->query(new MatchPhraseQuery('track_name', 'Trippy Mane'))
                ->build()
        )->asArray();

        $slopResponse = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->query(new MatchPhraseQuery('track_name', 'Trippy Mane', 1))
                ->build()
        )->asArray();

        $this->assertGreaterThanOrEqual(
            $exactResponse['hits']['total']['value'],
            $slopResponse['hits']['total']['value'],
        );
    }
}
