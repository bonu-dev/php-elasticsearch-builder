<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder;

use Bonu\ElasticsearchBuilder\Query\BoolQuery;
use Bonu\ElasticsearchBuilder\Query\QueryInterface;
use Bonu\ElasticsearchBuilder\Aggregation\AggregationInterface;
use Bonu\ElasticsearchBuilder\Exception\Builder\InvalidSizeException;
use Bonu\ElasticsearchBuilder\Exception\Builder\InvalidFromException;
use Bonu\ElasticsearchBuilder\Exception\Builder\AggregationAlreadyExistsException;

use function array_key_exists;

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
     * @var null|int
     */
    protected ?int $size = null;

    /**
     * @var null|int
     */
    protected ?int $from = null;

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

        if ($this->size !== null) {
            $payload['body']['size'] = $this->size;
        }

        if ($this->from !== null) {
            $payload['body']['from'] = $this->from;
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
     * @return static
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
     * @return static
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

    /**
     * @see https://www.elastic.co/docs/reference/elasticsearch/rest-apis/paginate-search-results
     *
     * @param int<1, max> $size
     *
     * @return static
     *
     * @throws \Bonu\ElasticsearchBuilder\Exception\Builder\InvalidSizeException
     */
    public function size(int $size): self
    {
        // Sanity check
        if ($size < 1) {
            throw new InvalidSizeException('Size must be greater than 0, ' . $size . ' given.');
        }

        $this->size = $size;
        return $this;
    }

    /**
     * @see https://www.elastic.co/docs/reference/elasticsearch/rest-apis/paginate-search-results
     *
     * @param int<0, max> $from
     *
     * @return static
     *
     * @throws \Bonu\ElasticsearchBuilder\Exception\Builder\InvalidFromException
     */
    public function from(int $from): self
    {
        // Sanity check
        if ($from < 0) {
            throw new InvalidFromException('From must be greater than or equal to 0, ' . $from . ' given.');
        }

        $this->from = $from;
        return $this;
    }
}
