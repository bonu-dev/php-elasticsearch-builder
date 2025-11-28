<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Integration\Query;

use Bonu\ElasticsearchBuilder\QueryBuilder;
use Bonu\ElasticsearchBuilder\Query\TermQuery;
use Bonu\ElasticsearchBuilder\Tests\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;

final class TermQueryTest extends IntegrationTestCase
{
    #[Test]
    public function itFiltersOutSpecificRecord(): void
    {
        $result = $this->client?->search(
            new QueryBuilder(self::INDEX)
                ->query(new TermQuery('track_id', '0L0LgwFZ7UtBnRNQvSBty6'))
                ->build()
        )->asArray();

        $this->assertSame(1, $result['hits']['total']['value']);
        $this->assertSame('0L0LgwFZ7UtBnRNQvSBty6', $result['hits']['hits'][0]['_source']['track_id']);
    }
}
