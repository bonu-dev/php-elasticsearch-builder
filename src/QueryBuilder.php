<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder;

use Bonu\ElasticsearchBuilder\Query\BoolQuery;
use Bonu\ElasticsearchBuilder\Query\QueryInterface;

/**
 * @internal
 */
final class QueryBuilder
{
    /**
     * @var null|\Bonu\ElasticsearchBuilder\Query\BoolQuery
     */
    private ?BoolQuery $query = null;

    /**
     * @param null|string $index
     */
    public function __construct(
        private readonly ?string $index = null,
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
        $body = [];

        if ($this->getIndex() !== null) {
            $body['index'] = $this->getIndex();
        }

        if ($this->query !== null) {
            $body['query'] = $this->query->toArray();
        }

        return $body;
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
