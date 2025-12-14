<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Aggregation;

/**
 * @see https://www.elastic.co/docs/reference/aggregations/search-aggregations-bucket-global-aggregation
 */
trait GlobalizableAggregation
{
    /**
     * @var bool
     */
    protected bool $global = false;

    /**
     * @return static
     */
    public function asGlobal(): static
    {
        $clone = clone $this;
        $clone->global = true;

        return $clone;
    }

    /**
     * @param array<string, mixed> $aggregation
     * @param string|\Stringable $name
     *
     * @return array<string, mixed>
     */
    protected function addGlobalToAggregation(array $aggregation, string | \Stringable $name): array
    {
        if ($this->global === false) {
            return $aggregation;
        }

        return [
            'global' => (object) [],
            'aggs' => [
                (string) $name => $aggregation,
            ],
        ];
    }
}
