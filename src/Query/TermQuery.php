<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Query;

/**
 * @see https://www.elastic.co/docs/reference/query-languages/query-dsl/query-dsl-term-query
 */
final class TermQuery implements QueryInterface
{
    use BoostableQuery;

    /**
     * @param string $field
     * @param int|float|string|bool $value
     */
    public function __construct(
        private readonly string $field,
        private readonly int|float|string|bool $value,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'term' => [
                $this->field => $this->addBoostToQuery([
                    'value' => $this->value,
                ]),
            ],
        ];
    }
}
