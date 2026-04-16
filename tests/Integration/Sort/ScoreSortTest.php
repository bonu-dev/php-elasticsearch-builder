<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Integration\Sort;

use PHPUnit\Framework\Attributes\Test;
use Bonu\ElasticsearchBuilder\QueryBuilder;
use Bonu\ElasticsearchBuilder\Sort\ScoreSort;
use Bonu\ElasticsearchBuilder\Query\MatchQuery;
use Bonu\ElasticsearchBuilder\Sort\SortDirectionEnum;
use Bonu\ElasticsearchBuilder\Tests\IntegrationTestCase;

use function count;

/**
 * @internal
 */
final class ScoreSortTest extends IntegrationTestCase
{
    #[Test]
    public function itSortsResultsByRelevanceScore(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->query(new MatchQuery('artist_name', 'Diplo'))
                ->sort(new ScoreSort(SortDirectionEnum::DESC))
                ->size(5)
                ->build()
        )->asArray();

        $hits = $response['hits']['hits'];
        $this->assertGreaterThan(0, count($hits));
        $counter = count($hits);

        for ($i = 1; $i < $counter; $i++) {
            $this->assertGreaterThanOrEqual($hits[$i]['_score'], $hits[$i - 1]['_score']);
        }
    }
}
