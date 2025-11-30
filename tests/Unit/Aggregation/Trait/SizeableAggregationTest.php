<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation\Trait;

use Bonu\ElasticsearchBuilder\Tests\TestCase;
use Bonu\ElasticsearchBuilder\Aggregation\SizeableAggregation;
use Bonu\ElasticsearchBuilder\Exception\Aggregation\InvalidAggregationSizeException;

final class SizeableAggregationTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function itThrowsExceptionIfSizeIsLessThan1(): void
    {
        $class = new class {
            use SizeableAggregation;
        };

        $this->expectException(InvalidAggregationSizeException::class);
        $class->size(0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function itDoesNotAddSizeIfItIsNotSet(): void
    {
        $class = new class {
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function itAddsSizeToAggregation(): void
    {
        $class = new class {
            use SizeableAggregation;

            /**
             * @return array<array-key, mixed>
             */
            public function toArray(): array
            {
                return $this->addSizeToAggregation([]);
            }
        };

        $class->size(10);
        $this->assertSame(['size' => 10], $class->toArray());
    }
}