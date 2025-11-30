<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Query;

interface QueryInterface
{
    /**
     * @return array<array-key, mixed>
     *
     * @throws \Bonu\ElasticsearchBuilder\Exception\Query\QueryException
     */
    public function toArray(): array;
}
