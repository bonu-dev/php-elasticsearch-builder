<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder;

use Elastic\Elasticsearch\Client;

/**
 * @internal
 */
final readonly class QueryBuilder
{
    /**
     * @param \Elastic\Elasticsearch\Client $client
     * @param null|string $index
     */
    public function __construct(
        private Client $client,
        private ?string $index = null,
    ) {
    }

    /**
     * @return \Elastic\Elasticsearch\Client
     */
    protected function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @return null|string
     */
    public function getIndex(): ?string
    {
        return $this->index;
    }

    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $body = [];

        if ($this->getIndex() !== null) {
            $body['index'] = $this->getIndex();
        }

        return $body;
    }
}
