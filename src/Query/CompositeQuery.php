<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Query;

abstract class CompositeQuery implements QueryInterface
{
    /**
     * @return \Bonu\ElasticsearchBuilder\Query\QueryInterface
     */
    abstract public function query(): QueryInterface;

    /**
     * @inheritDoc
     */
    #[\Override]
    public function toArray(): array
    {
        return $this->query()->toArray();
    }
}