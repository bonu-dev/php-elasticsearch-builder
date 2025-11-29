<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Query;

use Bonu\ElasticsearchBuilder\Exception\Query\EmptyBoolQueryException;

/**
 * @see https://www.elastic.co/docs/reference/query-languages/query-dsl/query-dsl-bool-query
 */
class BoolQuery implements QueryInterface
{
    use BoostableQuery;

    public const string TYPE_MUST = 'must';
    public const string TYPE_FILTER = 'filter';
    public const string TYPE_SHOULD = 'should';
    public const string TYPE_MUST_NOT = 'must_not';

    /**
     * @var array<self::TYPE_*, list<\Bonu\ElasticsearchBuilder\Query\QueryInterface>>
     */
    protected array $queries = [
        self::TYPE_MUST => [],
        self::TYPE_FILTER => [],
        self::TYPE_SHOULD => [],
        self::TYPE_MUST_NOT => [],
    ];

    /**
     * @return array<self::TYPE_*, list<\Bonu\ElasticsearchBuilder\Query\QueryInterface>>
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    /**
     * @return list<\Bonu\ElasticsearchBuilder\Query\QueryInterface>
     */
    public function getMustQueries(): array
    {
        return $this->getQueries()[self::TYPE_MUST];
    }

    /**
     * @return list<\Bonu\ElasticsearchBuilder\Query\QueryInterface>
     */
    public function getFilterQueries(): array
    {
        return $this->getQueries()[self::TYPE_FILTER];
    }

    /**
     * @return list<\Bonu\ElasticsearchBuilder\Query\QueryInterface>
     */
    public function getShouldQueries(): array
    {
        return $this->getQueries()[self::TYPE_SHOULD];
    }

    /**
     * @return list<\Bonu\ElasticsearchBuilder\Query\QueryInterface>
     */
    public function getMustNotQueries(): array
    {
        return $this->getQueries()[self::TYPE_MUST_NOT];
    }

    /**
     * @param \Bonu\ElasticsearchBuilder\Query\QueryInterface $query
     *
     * @return $this
     */
    public function must(QueryInterface $query): self
    {
        $this->mergeAddQuery($query, self::TYPE_MUST);
        return $this;
    }

    /**
     * @param \Bonu\ElasticsearchBuilder\Query\QueryInterface $query
     *
     * @return $this
     */
    public function filter(QueryInterface $query): self
    {
        $this->mergeAddQuery($query, self::TYPE_FILTER);
        return $this;
    }

    /**
     * @param \Bonu\ElasticsearchBuilder\Query\QueryInterface $query
     *
     * @return $this
     */
    public function should(QueryInterface $query): self
    {
        $this->mergeAddQuery($query, self::TYPE_SHOULD);
        return $this;
    }

    /**
     * @param \Bonu\ElasticsearchBuilder\Query\QueryInterface $query
     *
     * @return $this
     */
    public function mustNot(QueryInterface $query): self
    {
        $this->mergeAddQuery($query, self::TYPE_MUST_NOT);
        return $this;
    }

    /**
     * @param \Bonu\ElasticsearchBuilder\Query\QueryInterface $query
     * @param self::TYPE* $type
     */
    protected function mergeAddQuery(QueryInterface $query, string $type): void
    {
        if ($query instanceof self && ($query->getQueries()[$type] ?? []) !== []) {
            $this->queries[$type] = [
                ...($this->getQueries()[$type] ?? []),
                ...($query->getQueries()[$type] ?? []),
            ];

            return;
        }

        $this->queries[$type][] = $query;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $queries = array_filter([
            self::TYPE_MUST => array_map(
                static fn (QueryInterface $query): array => $query->toArray(),
                $this->getMustQueries(),
            ),
            self::TYPE_FILTER => array_map(
                static fn (QueryInterface $query): array => $query->toArray(),
                $this->getFilterQueries(),
            ),
            self::TYPE_SHOULD => array_map(
                static fn (QueryInterface $query): array => $query->toArray(),
                $this->getShouldQueries(),
            ),
            self::TYPE_MUST_NOT => array_map(
                static fn (QueryInterface $query): array => $query->toArray(),
                $this->getMustNotQueries(),
            ),
        ]);

        if ($queries === []) {
            throw new EmptyBoolQueryException('Bool query must have at least one sub-query.');
        }

        return [
            'bool' => $this->addBoostToQuery($queries),
        ];
    }
}
