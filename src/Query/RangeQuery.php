<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Query;

use Bonu\ElasticsearchBuilder\Exception\Query\InvalidRelationQueryException;

use function implode;
use function sprintf;
use function in_array;
use function array_filter;

/**
 * @see https://www.elastic.co/docs/reference/query-languages/query-dsl/query-dsl-range-query
 */
abstract class RangeQuery implements QueryInterface
{
    use BoostableQuery;

    public const RELATION_INTERSECTS = 'INTERSECTS';
    public const RELATION_CONTAINS = 'CONTAINS';
    public const RELATION_WITHIN = 'WITHIN';

    /**
     * @param string|\Stringable $field
     * @param null|float|int|string $lt
     * @param null|float|int|string $lte
     * @param null|float|int|string $gt
     * @param null|float|int|string $gte
     * @param null|string $format
     * @param null|string $relation
     * @param null|string $timeZone
     *
     */
    public function __construct(
        protected string | \Stringable $field,
        protected null | int | float | string $lt = null,
        protected null | int | float | string $lte = null,
        protected null | int | float | string $gt = null,
        protected null | int | float | string $gte = null,
        protected ?string $format = null,
        protected ?string $relation = null,
        protected ?string $timeZone = null,
    ) {
        if ($this->relation !== null && ! in_array($this->relation, [
            self::RELATION_INTERSECTS,
            self::RELATION_CONTAINS,
            self::RELATION_WITHIN,
        ], true)) {
            throw new InvalidRelationQueryException(sprintf(
                'Invalid relation for range query. Given "%s", expected one of [%s].',
                $this->relation,
                implode(', ', [
                    self::RELATION_INTERSECTS,
                    self::RELATION_CONTAINS,
                    self::RELATION_WITHIN,
                ]),
            ));
        }
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function toArray(): array
    {
        return [
            'range' => [
                (string) $this->field => $this->addBoostToQuery(array_filter([
                    'lt' => $this->lt,
                    'lte' => $this->lte,
                    'gt' => $this->gt,
                    'gte' => $this->gte,
                    'format' => $this->format,
                    'relation' => $this->relation,
                    'time_zone' => $this->timeZone,
                ])),
            ],
        ];
    }
}
