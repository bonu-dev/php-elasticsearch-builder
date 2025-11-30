<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;
use Bonu\ElasticsearchBuilder\QueryBuilder;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use PHPUnit\Framework\Attributes\DependsExternal;
use Bonu\ElasticsearchBuilder\Tests\Fixture\BoolQueryFixture;
use Bonu\ElasticsearchBuilder\Tests\Unit\Query\BoolQueryTest;

/**
 * @internal
 */
final class QueryBuilderTest extends TestCase
{
    #[Test]
    public function itReturnsIndex(): void
    {
        $this->assertSame('foo', new QueryBuilder('foo')->getIndex());
    }

    #[Test]
    public function itReturnsIndexInBody(): void
    {
        $this->assertSame([
            'body' => [],
            'index' => 'foo',
        ], new QueryBuilder('foo')->build());
    }

    #[Depends('itReturnsIndexInBody')]
    #[DependsExternal(BoolQueryTest::class, 'itCorrectlyBuildsArray')]
    #[Test]
    public function itReturnsQueryInBody(): void
    {
        $builder = new QueryBuilder('foo')
            ->query(new BoolQueryFixture('foo'));

        $this->assertSame([
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [['foo' => 'fixture_for_bool_query']],
                        'boost' => 1.0,
                    ],
                ],
            ],
            'index' => 'foo',
        ], $builder->build());
    }
}
