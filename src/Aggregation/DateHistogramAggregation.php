<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Aggregation;

use Bonu\ElasticsearchBuilder\Exception\Aggregation\InvalidDateHistogramIntervalException;

use function array_filter;

/**
 * @see https://www.elastic.co/docs/reference/aggregations/search-aggregations-bucket-datehistogram-aggregation
 */
class DateHistogramAggregation implements AggregationInterface
{
    use FilterableAggregation;
    use GlobalizableAggregation;

    /**
     * @param string|\Stringable $name
     * @param string|\Stringable $field
     * @param null|string $calendarInterval
     * @param null|string $fixedInterval
     * @param null|int $minDocCount
     * @param null|string $format
     * @param null|string $timeZone
     * @param null|string $offset
     *
     * @throws \Bonu\ElasticsearchBuilder\Exception\Aggregation\InvalidDateHistogramIntervalException
     */
    public function __construct(
        protected string | \Stringable $name,
        protected string | \Stringable $field,
        protected ?string $calendarInterval = null,
        protected ?string $fixedInterval = null,
        protected ?int $minDocCount = null,
        protected ?string $format = null,
        protected ?string $timeZone = null,
        protected ?string $offset = null,
    ) {
        if ($calendarInterval === null && $fixedInterval === null) {
            throw new InvalidDateHistogramIntervalException('Either calendarInterval or fixedInterval must be provided.');
        }

        if ($calendarInterval !== null && $fixedInterval !== null) {
            throw new InvalidDateHistogramIntervalException('Only one of calendarInterval or fixedInterval can be provided, not both.');
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
        $value = ['date_histogram' => array_filter([
            'field' => (string) $this->field,
            'calendar_interval' => $this->calendarInterval,
            'fixed_interval' => $this->fixedInterval,
            'min_doc_count' => $this->minDocCount,
            'format' => $this->format,
            'time_zone' => $this->timeZone,
            'offset' => $this->offset,
        ], static fn (mixed $value): bool => $value !== null)];
        $value = $this->addFilterToAggregation($value, $this->getName());
        $value = $this->addGlobalToAggregation($value, $this->getName());

        return [
            $this->getName() => $value,
        ];
    }
}
