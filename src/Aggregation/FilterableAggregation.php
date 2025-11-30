<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Aggregation;

use Bonu\ElasticsearchBuilder\Query\QueryInterface;

trait FilterableAggregation
{
    /**
     * @var null|\Bonu\ElasticsearchBuilder\Query\QueryInterface
     */
    protected ?QueryInterface $query = null;

    /**
     * @param \Bonu\ElasticsearchBuilder\Query\QueryInterface $query
     *
     * @return static
     */
    public function query(QueryInterface $query): static
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @param array<string, mixed> $aggregation
     *
     * @return array<string, mixed>
     */
    protected function addFilterToAggregation(array $aggregation): array
    {
        if ($this->query === null) {
            return $aggregation;
        }

        return [
            ...$aggregation,
        ];
    }
}