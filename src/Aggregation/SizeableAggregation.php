<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Aggregation;

trait SizeableAggregation
{
    /**
     * @var null|int
     */
    protected ?int $size = null;

    /**
     * @param int $size
     *
     * @return $this
     */
    public function size(int $size): static
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @param array<string, mixed> $aggregation
     *
     * @return array<string, mixed>
     */
    protected function addSizeToAggregation(array $aggregation): array
    {
        if ($this->size === null) {
            return $aggregation;
        }

        return [
            ...$aggregation,
            'size' => $this->size
        ];
    }
}