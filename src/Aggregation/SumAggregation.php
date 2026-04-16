<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Aggregation;

/**
 * @see https://www.elastic.co/docs/reference/aggregations/search-aggregations-metrics-sum-aggregation
 */
class SumAggregation implements AggregationInterface
{
    use FilterableAggregation;
    use GlobalizableAggregation;

    /**
     * @param string|\Stringable $name
     * @param string|\Stringable $field
     */
    public function __construct(
        protected string | \Stringable $name,
        protected string | \Stringable $field,
    ) {
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
        $value = ['sum' => ['field' => (string) $this->field]];
        $value = $this->addFilterToAggregation($value, $this->getName());
        $value = $this->addGlobalToAggregation($value, $this->getName());

        return [
            $this->getName() => $value,
        ];
    }
}
