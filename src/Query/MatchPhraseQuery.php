<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Query;

/**
 * @see https://www.elastic.co/docs/reference/query-languages/query-dsl/query-dsl-match-query-phrase
 */
final class MatchPhraseQuery implements QueryInterface
{
    use BoostableQuery;
    use AnalyzerAwareQuery;

    /**
     * @param string|\Stringable $field
     * @param int|float|string|bool $value
     * @param null|int $slop
     */
    public function __construct(
        private readonly string|\Stringable $field,
        private readonly int|float|string|bool $value,
        private readonly ?int $slop = null,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $value = [
            'query' => $this->value,
        ];

        if ($this->slop !== null) {
            $value['slop'] = $this->slop;
        }

        $value = $this->addBoostToQuery($value);
        $value = $this->addAnalyzerToQuery($value);

        return [
            'match_phrase' => [
                (string) $this->field => $value,
            ],
        ];
    }
}
