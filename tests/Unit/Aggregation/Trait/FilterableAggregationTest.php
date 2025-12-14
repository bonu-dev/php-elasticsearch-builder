<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation\Trait;

use PHPUnit\Framework\Attributes\Test;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use Bonu\ElasticsearchBuilder\Tests\Fixture\BoolQueryFixture;
use Bonu\ElasticsearchBuilder\Aggregation\FilterableAggregation;

/**
 * @internal
 */
final class FilterableAggregationTest extends TestCase
{
    #[Test]
    public function itDoesNothingIfNoQueryIsProvided(): void
    {
        $class = new class() {
            use FilterableAggregation;

            /**
             * @return array<array-key, mixed>
             */
            public function toArray(): array
            {
                return $this->addFilterToAggregation([], 'test');
            }
        };

        $this->assertSame([], $class->toArray());
    }

    #[Test]
    public function itAddsFilterToAggregation(): void
    {
        $class = new class() {
            use FilterableAggregation;

            /**
             * @return array<array-key, mixed>
             */
            public function toArray(): array
            {
                return $this->addFilterToAggregation([
                    'baz' => 'bar',
                ], 'test');
            }
        };

        $class = $class->query(new BoolQueryFixture('foo'));

        $this->assertSame([
            'filter' => [
                'foo' => 'fixture_for_bool_query',
            ],
            'aggs' => [
                'test' => [
                    'baz' => 'bar',
                ],
            ],
        ], $class->toArray());
    }
}
