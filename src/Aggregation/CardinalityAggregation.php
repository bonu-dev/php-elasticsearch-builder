<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Aggregation;

use Bonu\ElasticsearchBuilder\Exception\Aggregation\InvalidPrecisionThresholdException;

use function array_filter;

/**
 * @see https://www.elastic.co/docs/reference/aggregations/search-aggregations-metrics-cardinality-aggregation
 */
class CardinalityAggregation implements AggregationInterface
{
    use FilterableAggregation;
    use GlobalizableAggregation;

    /**
     * @param string|\Stringable $name
     * @param string|\Stringable $field
     * @param null|int $precisionThreshold
     *
     * @throws \Bonu\ElasticsearchBuilder\Exception\Aggregation\InvalidPrecisionThresholdException
     */
    public function __construct(
        protected string | \Stringable $name,
        protected string | \Stringable $field,
        protected ?int $precisionThreshold = null,
    ) {
        if ($precisionThreshold !== null && $precisionThreshold < 1) {
            throw new InvalidPrecisionThresholdException('Precision threshold must be a positive integer. ' . $precisionThreshold . ' given.');
        }
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getName(): string
    {
        return (string) $this->name;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function toArray(): array
    {
        $value = ['cardinality' => array_filter([
            'field' => (string) $this->field,
            'precision_threshold' => $this->precisionThreshold,
        ], static fn (mixed $value): bool => $value !== null)];
        $value = $this->addFilterToAggregation($value, $this->getName());
        $value = $this->addGlobalToAggregation($value, $this->getName());

        return [
            $this->getName() => $value,
        ];
    }
}
