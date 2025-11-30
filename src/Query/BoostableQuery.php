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
     * @return $this
     */
    public function boost(float $boost): static
    {
        $this->boost = $boost;
        return $this;
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     */
    protected function addBoostToQuery(array $query): array
    {
        if ($this->boost === null) {
            return $query;
        }

        return [
            ...$query,
            'boost' => $this->boost,
        ];
    }
}
