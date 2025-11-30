<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Aggregation;

interface AggregationInterface
{
    /**
     * @return bool
     */
    public function isGlobal(): bool;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return array<array-key, mixed>
     */
    public function toArray(): array;
}
