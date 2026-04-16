<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Query;

/**
 * @see https://www.elastic.co/docs/reference/query-languages/query-dsl/query-dsl-exists-query
 */
class ExistsQuery implements QueryInterface
{
    use BoostableQuery;

    /**
     * @param string|\Stringable $field
     */
    public function __construct(
        protected string | \Stringable $field,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'exists' => $this->addBoostToQuery([
                'field' => (string) $this->field,
            ]),
        ];
    }
}
