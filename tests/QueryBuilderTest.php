<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests;

use Elastic\Elasticsearch\ClientBuilder;
use Bonu\ElasticsearchBuilder\QueryBuilder;
use PHPUnit\Framework\Attributes\Test;

final class QueryBuilderTest extends TestCase
{
    #[Test]
    public function itReturnsIndexInBody(): void
    {
        $builder = new QueryBuilder(ClientBuilder::create()->build(), 'foo');
        $body = $builder->build();

        $this->assertArrayHasKey('index', $body);
        $this->assertEquals('foo', $body['index']);
    }
}
