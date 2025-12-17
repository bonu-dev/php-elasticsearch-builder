<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Aggregation;

use Bonu\ElasticsearchBuilder\Exception\Aggregation\DuplicatedNestedAggregationException;

use function array_key_exists;
use function iterator_to_array;

/**
 * @see https://www.elastic.co/docs/reference/aggregations/search-aggregations-bucket-nested-aggregation
 */
class NestedAggregation implements AggregationInterface
{
    use FilterableAggregation;
    use GlobalizableAggregation;

    /**
     * @var array<string, \Bonu\ElasticsearchBuilder\Aggregation\AggregationInterface>
     */
    protected array $aggregations = [];

    /**
     * @param string|\Stringable $name
     * @param string|\Stringable $path
     */
    public function __construct(
        protected string | \Stringable $name,
        protected string | \Stringable $path,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return (string) $this->name;
    }

    /**
     * @param \Bonu\ElasticsearchBuilder\Aggregation\AggregationInterface $aggregation
     *
     * @return static
     *
     * @throws \Bonu\ElasticsearchBuilder\Exception\Aggregation\DuplicatedNestedAggregationException
     */
    public function aggregation(AggregationInterface $aggregation): static
    {
        if (array_key_exists($aggregation->getName(), $this->aggregations)) {
            throw new DuplicatedNestedAggregationException(
                'Nested aggregation with name "' . $aggregation->getName() . '" already exists.',
            );
        }

        $clone = clone $this;
        $clone->aggregations[$aggregation->getName()] = $aggregation;
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $value = [
            'nested' => [
                'path' => (string) $this->path,
            ],
            'aggs' => iterator_to_array($this->mapAggregations()),
        ];
        $value = $this->addFilterToAggregation($value, $this->getName());
        $value = $this->addGlobalToAggregation($value, $this->getName());

        return [
            $this->getName() => $value,
        ];
    }

    /**
     * @return \Generator<array-key, mixed>
     */
    protected function mapAggregations(): \Generator
    {
        foreach ($this->aggregations as $aggregation) {
            yield from $aggregation->toArray();
        }
    }
}
