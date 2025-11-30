<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;

use function getenv;

abstract class IntegrationTestCase extends TestCase
{
    final public const INDEX = 'spotify';

    /**
     * @var null|\Elastic\Elasticsearch\Client
     */
    protected ?Client $client = null;

    /**
     * @inheritDoc
     *
     * @throws \Elastic\Elasticsearch\Exception\AuthenticationException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->client = ClientBuilder::create()
            ->setHosts([getenv('ELASTICSEARCH_HOST')])
            ->build();
    }
}
