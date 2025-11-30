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
     * @return $this
     */
    public function analyzer(string $analyzer): static
    {
        $this->analyzer = $analyzer;
        return $this;
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
