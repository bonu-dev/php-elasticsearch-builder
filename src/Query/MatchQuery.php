<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Query;

use Bonu\ElasticsearchBuilder\Exception\Query\InvalidOperatorQueryException;

use function implode;
use function in_array;
use function sprintf;

/**
 * @see https://www.elastic.co/docs/reference/query-languages/query-dsl/query-dsl-match-query
 */
class MatchQuery implements QueryInterface
{
    use BoostableQuery;
    use AnalyzerAwareQuery;

    public const string OPERATOR_OR = 'OR';
    public const string OPERATOR_AND = 'AND';

    /**
     * @param string|\Stringable $field
     * @param int|float|string|bool $value
     * @param self::OPERATOR_* $operator
     *
     * @throws \Bonu\ElasticsearchBuilder\Exception\Query\InvalidOperatorQueryException
     */
    public function __construct(
        protected string|\Stringable $field,
        protected int|float|string|bool $value,
        protected string $operator = self::OPERATOR_OR
    ) {
        if (!in_array($operator, [self::OPERATOR_OR, self::OPERATOR_AND], true)) {
            throw new InvalidOperatorQueryException(sprintf(
                'Invalid operator for match query. Given "%s", expected one of [%s].',
                $operator,
                implode(', ', [self::OPERATOR_OR, self::OPERATOR_AND]),
            ));
        }
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $value = [
            'query' => $this->value,
            'operator' => $this->operator,
        ];
        $value = $this->addBoostToQuery($value);
        $value = $this->addAnalyzerToQuery($value);

        return [
            'match' => [
                (string) $this->field => $value,
            ],
        ];
    }
}
