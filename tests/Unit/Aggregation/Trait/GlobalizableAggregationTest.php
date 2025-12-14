<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation\Trait;

use PHPUnit\Framework\Attributes\Test;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use Bonu\ElasticsearchBuilder\Aggregation\GlobalizableAggregation;

/**
 * @internal
 */
final class GlobalizableAggregationTest extends TestCase
{
    #[Test]
    public function itDoesNothingIfNoQueryIsProvided(): void
    {
        $class = new class() {
            use GlobalizableAggregation;

            /**
             * @return array<array-key, mixed>
             */
            public function toArray(): array
            {
                return $this->addGlobalToAggregation([], 'test');
            }
        };

        $this->assertSame([], $class->toArray());
    }

    #[Test]
    public function itAddsGlobalToAggregation(): void
    {
        $class = new class() {
            use GlobalizableAggregation;

            /**
             * @return array<array-key, mixed>
             */
            public function toArray(): array
            {
                return $this->addGlobalToAggregation([
                    'baz' => 'bar',
                ], 'test');
            }
        };

        $class = $class->asGlobal();

        $this->assertEquals([
            'global' => (object) [],
            'aggs' => [
                'test' => [
                    'baz' => 'bar',
                ],
            ],
        ], $class->toArray());
    }
}
