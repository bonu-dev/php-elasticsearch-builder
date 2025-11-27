<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Fixture;

use Bonu\ElasticsearchBuilder\Query\QueryInterface;

final readonly class BoolQueryFixture implements QueryInterface
{
    /**
     * @param string $name
     */
    public function __construct(
        private string $name,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            $this->name => 'fixture_for_bool_query',
        ];
    }
}
