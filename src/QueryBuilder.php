<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder;

use Bonu\ElasticsearchBuilder\Query\BoolQuery;
use Bonu\ElasticsearchBuilder\Query\QueryInterface;
use Bonu\ElasticsearchBuilder\Aggregation\AggregationInterface;
use Bonu\ElasticsearchBuilder\Exception\Builder\AggregationAlreadyExistsException;

use function array_key_exists;

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
     * @var array<string, \Bonu\ElasticsearchBuilder\Aggregation\AggregationInterface>
     */
    protected array $aggregations = [];

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

        if ($this->aggregations !== []) {
            $payload['body']['aggs'] = [];

            foreach ($this->aggregations as $aggregation) {
                $payload['body']['aggs'] = [
                    ...$payload['body']['aggs'],
                    ...$aggregation->toArray(),
                ];
            }
        }

        return $payload;
    }

    /**
     * @param \Bonu\ElasticsearchBuilder\Query\QueryInterface $query
     *
     * @return $this
     */
    public function query(QueryInterface $query): self
    {
        $this->query = ($this->query ?? new BoolQuery())->must(
            $query,
        );

        return $this;
    }

    /**
     * @param \Bonu\ElasticsearchBuilder\Aggregation\AggregationInterface $aggregation
     *
     * @return $this
     *
     * @throws \Bonu\ElasticsearchBuilder\Exception\Builder\AggregationAlreadyExistsException
     */
    public function aggregation(AggregationInterface $aggregation): self
    {
        if (array_key_exists($aggregation->getName(), $this->aggregations)) {
            throw new AggregationAlreadyExistsException(
                'Aggregation with name "' . $aggregation->getName() . '" already exists.',
            );
        }

        $this->aggregations[$aggregation->getName()] = $aggregation;
        return $this;
    }
}
