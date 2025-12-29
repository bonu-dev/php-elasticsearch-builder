<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Aggregation;

use Bonu\ElasticsearchBuilder\Exception\Aggregation\InvalidContainerAggregationException;
use Bonu\ElasticsearchBuilder\Exception\Aggregation\DuplicatedContainerAggregationException;

use function array_key_exists;
use function iterator_to_array;

class ContainerAggregation implements AggregationInterface
{
    use FilterableAggregation;
    use GlobalizableAggregation;

    /**
     * @var array<string, \Bonu\ElasticsearchBuilder\Aggregation\AggregationInterface>
     */
    protected array $aggregations = [];

    /**
     * @param string|\Stringable $name
     */
    public function __construct(
        protected string | \Stringable $name,
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
     * @throws \Bonu\ElasticsearchBuilder\Exception\Aggregation\DuplicatedContainerAggregationException
     */
    public function aggregation(AggregationInterface $aggregation): static
    {
        if (array_key_exists($aggregation->getName(), $this->aggregations)) {
            throw new DuplicatedContainerAggregationException(
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
        if ($this->query === null && $this->global === false) {
            throw new InvalidContainerAggregationException('Container aggregation must have a query or global set.');
        }

        if ($this->query !== null && $this->global) {
            throw new InvalidContainerAggregationException('Container aggregation must have a query or global set, not both.');
        }

        $value = [
            'aggs' => iterator_to_array($this->mapAggregations()),
        ];

        if ($this->global) {
            $value['global'] = (object) [];
        }

        if ($this->query !== null) {
            $value['filter'] = $this->query->toArray();
        }

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
