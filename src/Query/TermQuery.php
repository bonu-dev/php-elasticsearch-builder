<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Query;

/**
 * @see https://www.elastic.co/docs/reference/query-languages/query-dsl/query-dsl-term-query
 */
class TermQuery implements QueryInterface
{
    use BoostableQuery;

    /**
     * @param string|\Stringable $field
     * @param int|float|string|bool $value
     */
    public function __construct(
        protected string|\Stringable $field,
        protected int|float|string|bool $value,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'term' => [
                (string) $this->field => $this->addBoostToQuery([
                    'value' => $this->value,
                ]),
            ],
        ];
    }
}
