<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Sort;

use function array_filter;

/**
 * @see https://www.elastic.co/docs/reference/elasticsearch/rest-apis/sort-search-results
 */
class FieldSort implements SortInterface
{
    /**
     * @param string|\Stringable $field
     * @param \Bonu\ElasticsearchBuilder\Sort\SortDirectionEnum $direction
     * @param null|string $format
     */
    public function __construct(
        private string | \Stringable $field,
        private SortDirectionEnum $direction = SortDirectionEnum::ASC,
        private ?string $format = null
    ) {
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function toArray(): array
    {
        return [
            (string) $this->field => array_filter([
                'order' => $this->direction->value,
                'format' => $this->format,
            ], static fn (mixed $value): bool => $value !== null),
        ];
    }
}
