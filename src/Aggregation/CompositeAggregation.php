<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Aggregation;

abstract class CompositeAggregation implements AggregationInterface
{
    /**
     * @return \Bonu\ElasticsearchBuilder\Aggregation\AggregationInterface
     */
    abstract public function aggregation(): AggregationInterface;

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->aggregation()->getName();
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->aggregation()->toArray();
    }
}
