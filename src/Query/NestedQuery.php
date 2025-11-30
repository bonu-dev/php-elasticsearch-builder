<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Query;

use Bonu\ElasticsearchBuilder\Exception\Query\EmptyNestedQueryException;

/**
 * @see https://www.elastic.co/docs/reference/query-languages/query-dsl/query-dsl-nested-query
 */
class NestedQuery implements QueryInterface
{
    /**
     * @var null|\Bonu\ElasticsearchBuilder\Query\QueryInterface
     */
    protected ?QueryInterface $query = null;

    /**
     * @param string|\Stringable $path
     */
    public function __construct(
        protected string | \Stringable $path,
    ) {
    }

    /**
     * @param \Bonu\ElasticsearchBuilder\Query\QueryInterface $query
     *
     * @return static
     */
    public function query(QueryInterface $query): static
    {
        $clone = clone $this;
        $clone->query = $query;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        if ($this->query === null) {
            throw new EmptyNestedQueryException('Nested query must have a query set.');
        }

        return [
            'nested' => [
                'path' => (string) $this->path,
                'query' => $this->query->toArray(),
            ],
        ];
    }
}
