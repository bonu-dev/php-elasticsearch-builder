<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation\Trait;

use PHPUnit\Framework\Attributes\Test;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use Bonu\ElasticsearchBuilder\Aggregation\SizeableAggregation;
use Bonu\ElasticsearchBuilder\Exception\Aggregation\InvalidAggregationSizeException;

/**
 * @internal
 */
final class SizeableAggregationTest extends TestCase
{
    #[Test]
    public function itThrowsExceptionIfSizeIsLessThan1(): void
    {
        $class = new class() {
            use SizeableAggregation;
        };

        $this->expectException(InvalidAggregationSizeException::class);
        $class->size(0);
    }

    #[Test]
    public function itDoesNotAddSizeIfItIsNotSet(): void
    {
        $class = new class() {
            use SizeableAggregation;

            /**
             * @return array<array-key, mixed>
             */
            public function toArray(): array
            {
                return $this->addSizeToAggregation([]);
            }
        };

        $this->assertSame([], $class->toArray());
    }

    #[Test]
    public function itAddsSizeToAggregation(): void
    {
        $class = new class() {
            use SizeableAggregation;

            /**
             * @return array<array-key, mixed>
             */
            public function toArray(): array
            {
                return $this->addSizeToAggregation([]);
            }
        };

        $class = $class->size(10);
        $this->assertSame(['size' => 10], $class->toArray());
    }
}
