<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Query;

/**
 * @see https://www.elastic.co/docs/reference/query-languages/query-dsl/query-dsl-range-query
 */
class DatetimeRangeQuery extends RangeQuery
{
    /**
     * @param string|\Stringable $field
     * @param null|string $lt
     * @param null|string $lte
     * @param null|string $gt
     * @param null|string $gte
     * @param null|string $format
     * @param self::RELATION_* $relation
     * @param null|string $timeZone
     *
     * @throws \Bonu\ElasticsearchBuilder\Exception\Query\InvalidRelationQueryException
     */
    public function __construct(
        string | \Stringable $field,
        null | string $lt = null,
        null | string $lte = null,
        null | string $gt = null,
        null | string $gte = null,
        protected null | string $format = null,
        protected string $relation = self::RELATION_INTERSECTS,
        protected null|string $timeZone = null,
    ) {
        parent::__construct(
            $field,
            $lt,
            $lte,
            $gt,
            $gte,
            $format,
            $relation,
            $timeZone,
        );
    }
}
