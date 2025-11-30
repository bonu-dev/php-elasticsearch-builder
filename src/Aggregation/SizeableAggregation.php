<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Aggregation;

use Bonu\ElasticsearchBuilder\Exception\Aggregation\InvalidAggregationSizeException;

trait SizeableAggregation
{
    /**
     * @var null|int
     */
    protected ?int $size = null;

    /**
     * @param int $size
     *
     * @return static
     */
    public function size(int $size): static
    {
        if ($size < 1) {
            throw new InvalidAggregationSizeException('Size must be greater than 0, ' . $size . ' given.');
        }

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
            'size' => $this->size,
        ];
    }
}
