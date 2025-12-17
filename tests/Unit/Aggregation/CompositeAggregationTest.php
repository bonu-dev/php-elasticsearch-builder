<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation;

use PHPUnit\Framework\Attributes\Test;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use PHPUnit\Framework\Attributes\DependsExternal;
use Bonu\ElasticsearchBuilder\Aggregation\TermsAggregation;
use Bonu\ElasticsearchBuilder\Aggregation\AggregationInterface;
use Bonu\ElasticsearchBuilder\Aggregation\CompositeAggregation;

/**
 * @internal
 */
final class CompositeAggregationTest extends TestCase
{
    #[DependsExternal(TermsAggregationTest::class, 'itBuildsBasicTermsAggregation')]
    #[Test]
    public function itBuildsArrayAndNameFromAbstractAggregationMethod(): void
    {
        $composite = new class() extends CompositeAggregation {
            /**
             * @inheritDoc
             */
            public function aggregation(): AggregationInterface
            {
                return new TermsAggregation('tags', 'category');
            }
        };

        $this->assertSame('tags', $composite->getName());

        $this->assertSame([
            'tags' => [
                'terms' => [
                    'field' => 'category',
                ],
            ],
        ], $composite->toArray());
    }
}
