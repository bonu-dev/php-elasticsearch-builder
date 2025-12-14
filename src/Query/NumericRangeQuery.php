<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Query;

/**
 * @see https://www.elastic.co/docs/reference/query-languages/query-dsl/query-dsl-range-query
 */
class NumericRangeQuery extends RangeQuery
{
    /**
     * @param string|\Stringable $field
     * @param null|float|int $lt
     * @param null|float|int $lte
     * @param null|float|int $gt
     * @param null|float|int $gte
     * @param self::RELATION_* $relation
     *
     * @throws \Bonu\ElasticsearchBuilder\Exception\Query\InvalidRelationQueryException
     */
    public function __construct(
        string | \Stringable $field,
        null | int | float $lt = null,
        null | int | float $lte = null,
        null | int | float $gt = null,
        null | int | float $gte = null,
        string $relation = self::RELATION_INTERSECTS,
    ) {
        parent::__construct($field, $lt, $lte, $gt, $gte, relation: $relation);
    }
}
