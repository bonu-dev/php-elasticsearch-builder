<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Aggregation;

/**
 * @see https://www.elastic.co/docs/reference/aggregations/search-aggregations-bucket-terms-aggregation
 */
class TermsAggregation implements AggregationInterface
{
    use SizeableAggregation;
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
    public function getName(): string
    {
        return (string) $this->name;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $value = ['field' => (string) $this->field];
        $value = $this->addSizeToAggregation($value);
        $value = ['terms' => $value];
        $value = $this->addFilterToAggregation($value, $this->getName());
        $value = $this->addGlobalToAggregation($value, $this->getName());

        return [
            $this->getName() => $value,
        ];
    }
}
