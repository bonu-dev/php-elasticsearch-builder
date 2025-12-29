<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Aggregation;

use Bonu\ElasticsearchBuilder\Exception\Aggregation\InvalidIntervalException;

use function array_filter;

/**
 * @see https://www.elastic.co/docs/reference/aggregations/search-aggregations-bucket-histogram-aggregation
 */
class HistogramAggregation implements AggregationInterface
{
    use FilterableAggregation;
    use GlobalizableAggregation;

    /**
     * @param string|\Stringable $name
     * @param string|\Stringable $field
     * @param float|int $interval
     * @param null|int $minDocCount
     */
    public function __construct(
        protected string | \Stringable $name,
        protected string | \Stringable $field,
        protected int | float $interval = 10,
        protected ?int $minDocCount = null
    ) {
        if ($interval < 1) {
            throw new InvalidIntervalException('Interval must be a positive number. ' . $interval . ' given.');
        }
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return (string) $this->name;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $value = ['histogram' => array_filter([
            'field' => (string) $this->field,
            'interval' => $this->interval,
            'min_doc_count' => $this->minDocCount,
        ], static fn (mixed $value): bool => $value !== null)];
        $value = $this->addFilterToAggregation($value, $this->getName());
        $value = $this->addGlobalToAggregation($value, $this->getName());

        return [
            $this->getName() => $value,
        ];
    }
}
