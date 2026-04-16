<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Query;

use Bonu\ElasticsearchBuilder\Exception\Query\EmptyTermsQueryException;

/**
 * @see https://www.elastic.co/docs/reference/query-languages/query-dsl/query-dsl-terms-query
 */
class TermsQuery implements QueryInterface
{
    use BoostableQuery;

    /**
     * @param string|\Stringable $field
     * @param list<bool|float|int|string> $values
     *
     * @throws \Bonu\ElasticsearchBuilder\Exception\Query\EmptyTermsQueryException
     */
    public function __construct(
        protected string | \Stringable $field,
        protected array $values,
    ) {
        if ($values === []) {
            throw new EmptyTermsQueryException('Terms query must have at least one value.');
        }
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'terms' => $this->addBoostToQuery([
                (string) $this->field => $this->values,
            ]),
        ];
    }
}
