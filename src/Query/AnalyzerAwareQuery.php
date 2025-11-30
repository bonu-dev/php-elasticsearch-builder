<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Query;

trait AnalyzerAwareQuery
{
    /**
     * @var null|string
     */
    protected ?string $analyzer = null;

    /**
     * @param string $analyzer
     *
     * @return static
     */
    public function analyzer(string $analyzer): static
    {
        $clone = clone $this;
        $clone->analyzer = $analyzer;

        return $clone;
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     */
    protected function addAnalyzerToQuery(array $query): array
    {
        if ($this->analyzer === null) {
            return $query;
        }

        return [
            ...$query,
            'analyzer' => $this->analyzer,
        ];
    }
}
