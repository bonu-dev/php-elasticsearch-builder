<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Sort;

/**
 * @see https://www.elastic.co/docs/reference/elasticsearch/rest-apis/sort-search-results
 */
final class ScoreSort implements SortInterface
{
    /**
     * @param \Bonu\ElasticsearchBuilder\Sort\SortDirectionEnum $direction
     */
    public function __construct(
        private SortDirectionEnum $direction = SortDirectionEnum::DESC,
    ) {
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function toArray(): array
    {
        return new FieldSort('_score', $this->direction)->toArray();
    }
}
