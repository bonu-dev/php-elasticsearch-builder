<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Query;

trait BoostableQuery
{
    /**
     * @var float
     */
    protected float $boost = 1.0;

    /**
     * @param float $boost
     *
     * @return static
     */
    public function boost(float $boost): static
    {
        $clone = clone $this;
        $clone->boost = $boost;

        return $clone;
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     */
    protected function addBoostToQuery(array $query): array
    {
        return [
            ...$query,
            'boost' => $this->boost,
        ];
    }
}
