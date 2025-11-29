<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Aggregation;

trait GlobalizableAggregation
{
    /**
     * @var bool
     */
    protected bool $global = false;

    /**
     * @return bool
     */
    public function isGlobal(): bool
    {
        return $this->global;
    }

    /**
     * @return $this
     */
    public function asGlobal(): static
    {
        $this->global = true;
        return $this;
    }
}
