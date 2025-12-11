<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Integration\Query;

use PHPUnit\Framework\Attributes\Test;
use Bonu\ElasticsearchBuilder\QueryBuilder;
use Bonu\ElasticsearchBuilder\Query\TermQuery;
use Bonu\ElasticsearchBuilder\Tests\IntegrationTestCase;

/**
 * @internal
 */
final class TermQueryTest extends IntegrationTestCase
{
    #[Test]
    public function itFiltersOutSpecificRecord(): void
    {
        $response = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->query(new TermQuery('track_id', '0L0LgwFZ7UtBnRNQvSBty6'))
                ->build()
        )->asArray();

        $this->assertSame(1, $response['hits']['total']['value']);
        $this->assertSame('0L0LgwFZ7UtBnRNQvSBty6', $response['hits']['hits'][0]['_source']['track_id']);
    }
}
