<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Sort;

interface SortInterface
{
    /**
     * @return array<array-key, mixed>
     */
    public function toArray(): array;
}
