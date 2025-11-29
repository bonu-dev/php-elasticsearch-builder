<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder;

use Bonu\ElasticsearchBuilder\Query\BoolQuery;
use Bonu\ElasticsearchBuilder\Query\QueryInterface;

/**
 * @internal
 */
class QueryBuilder
{
    /**
     * @var null|\Bonu\ElasticsearchBuilder\Query\BoolQuery
     */
    protected ?BoolQuery $query = null;

    /**
     * @param null|string $index
     */
    public function __construct(
        protected ?string $index = null,
    ) {
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
     *
     * @throws \Bonu\ElasticsearchBuilder\Exception\Query\QueryException
     */
    public function build(): array
    {
        $payload = [
            'body' => [],
        ];

        if ($this->getIndex() !== null) {
            $payload['index'] = $this->getIndex();
        }

        if ($this->query !== null) {
            $payload['body']['query'] = $this->query->toArray();
        }

        return $payload;
    }

    /**
     * @param \Bonu\ElasticsearchBuilder\Query\QueryInterface $query
     *
     * @return self
     */
    public function query(QueryInterface $query): self
    {
        if ($this->query === null) {
            $this->query = new BoolQuery();
        }

        $this->query->must($query);
        return $this;
    }
}
