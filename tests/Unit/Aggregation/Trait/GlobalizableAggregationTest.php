<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation\Trait;

use Bonu\ElasticsearchBuilder\Tests\TestCase;
use Bonu\ElasticsearchBuilder\Aggregation\GlobalizableAggregation;

final class GlobalizableAggregationTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function itCorrentlySetsIfAggregationIsGlobal(): void
    {
        $fixture = new class {
            use GlobalizableAggregation;
        };

        $this->assertFalse($fixture->isGlobal());

        $fixture->asGlobal();
        $this->assertTrue($fixture->isGlobal());
    }
}