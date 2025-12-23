<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Aggregation;

use Bonu\ElasticsearchBuilder\Exception\Aggregation\NotEnoughFieldsAggregationException;

use function count;
use function array_map;

/**
 * @see https://www.elastic.co/docs/reference/aggregations/search-aggregations-bucket-multi-terms-aggregation
 */
class MultiTermsAggregation implements AggregationInterface
{
    use SizeableAggregation;
    use FilterableAggregation;
    use GlobalizableAggregation;

    /**
     * @param string|\Stringable $name
     * @param list<string|\Stringable> $fields
     *
     * @throws \Bonu\ElasticsearchBuilder\Exception\Aggregation\NotEnoughFieldsAggregationException
     */
    public function __construct(
        protected string | \Stringable $name,
        protected array $fields,
    ) {
        if (count($fields) < 2) {
            throw new NotEnoughFieldsAggregationException('At least two fields are required for MultiTermsAggregation.');
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
        $value = ['terms' => array_map(
            static fn (string | \Stringable $field): array => ['field' => (string) $field],
            $this->fields,
        )];
        $value = $this->addSizeToAggregation($value);
        $value = ['multi_terms' => $value];
        $value = $this->addFilterToAggregation($value, $this->getName());
        $value = $this->addGlobalToAggregation($value, $this->getName());

        return [
            $this->getName() => $value,
        ];
    }
}
