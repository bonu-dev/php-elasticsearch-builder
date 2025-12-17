<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Aggregation;

abstract class CompositeAggregation implements AggregationInterface
{
    /**
     * @inheritDoc
     */
    #[\Override]
    public function getName(): string
    {
        // TODO: Implement getName() method.
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function toArray(): array
    {
        // TODO: Implement toArray() method.
    }
}