<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation;

use PHPUnit\Framework\Attributes\Test;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use PHPUnit\Framework\Attributes\DependsExternal;
use Bonu\ElasticsearchBuilder\Aggregation\TermsAggregation;
use Bonu\ElasticsearchBuilder\Tests\Fixture\BoolQueryFixture;
use Bonu\ElasticsearchBuilder\Aggregation\ContainerAggregation;
use Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation\Trait\FilterableAggregationTest;
use Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation\Trait\GlobalizableAggregationTest;
use Bonu\ElasticsearchBuilder\Exception\Aggregation\InvalidContainerAggregationException;
use Bonu\ElasticsearchBuilder\Exception\Aggregation\DuplicatedContainerAggregationException;

/**
 * @internal
 */
final class ContainerAggregationTest extends TestCase
{
    #[Test]
    public function itThrowsExceptionWhenNoQueryOrGlobalIsSet(): void
    {
        $this->expectException(InvalidContainerAggregationException::class);

        new ContainerAggregation('my_container')->toArray();
    }

    #[Test]
    public function itThrowsExceptionWhenQueryAndGlobalIsSet(): void
    {
        $this->expectException(InvalidContainerAggregationException::class);

        new ContainerAggregation('my_container')
            ->asGlobal()
            ->query(new BoolQueryFixture('foo'))
            ->toArray();
    }

    #[Test]
    public function itThrowsExceptionIfDuplicatedAggregationIsBeingAdded(): void
    {
        $this->expectException(DuplicatedContainerAggregationException::class);

        $agg = new ContainerAggregation('my_container');
        $agg = $agg->aggregation(new TermsAggregation('foo', 'bar'));
        $agg->aggregation(new TermsAggregation('foo', 'bar'));
    }

    #[DependsExternal(GlobalizableAggregationTest::class, 'itAddsGlobalToAggregation')]
    #[Test]
    public function itBuildsBasicAggregationWithGlobal(): void
    {
        $agg = new ContainerAggregation('my_container')->asGlobal();

        $this->assertEquals([
            'my_container' => [
                'global' => (object) [],
                'aggs' => [],
            ],
        ], $agg->toArray());
    }

    #[DependsExternal(FilterableAggregationTest::class, 'itAddsFilterToAggregation')]
    #[Test]
    public function itBuildsBasicAggregationWithQuery(): void
    {
        $agg = new ContainerAggregation('my_container')->query(new BoolQueryFixture('foo'));

        $this->assertSame([
            'my_container' => [
                'aggs' => [],
                'filter' => [
                    'foo' => 'fixture_for_bool_query',
                ],
            ],
        ], $agg->toArray());
    }

    #[DependsExternal(GlobalizableAggregationTest::class, 'itAddsGlobalToAggregation')]
    #[DependsExternal(TermsAggregationTest::class, 'itBuildsBasicTermsAggregation')]
    #[Test]
    public function itBuildsWithNestedAggregations(): void
    {
        $agg = new ContainerAggregation('my_container')
            ->asGlobal()
            ->aggregation(new TermsAggregation('foo', 'bar'))
            ->aggregation(new TermsAggregation('bar', 'baz'));

        $this->assertEquals([
            'my_container' => [
                'global' => (object) [],
                'aggs' => [
                    'foo' => [
                        'terms' => [
                            'field' => 'bar',
                        ],
                    ],
                    'bar' => [
                        'terms' => [
                            'field' => 'baz',
                        ],
                    ],
                ],
            ],
        ], $agg->toArray());
    }
}
