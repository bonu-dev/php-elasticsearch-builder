<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Query;

interface QueryInterface
{
    /**
     * @return array<array-key, mixed>
     */
    public function toArray(): array;
}
