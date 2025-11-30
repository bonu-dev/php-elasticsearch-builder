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
    public function toArray(): array
    {
        $value = [
            'field' => (string) $this->field,
        ];
        $value = $this->addSizeToAggregation($value);

        return [
            (string) $this->name => [
                'terms' => $value,
            ],
        ];
    }
}
