<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Query;

use PHPUnit\Framework\Attributes\Test;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use Bonu\ElasticsearchBuilder\Query\TermQuery;
use PHPUnit\Framework\Attributes\DependsExternal;
use Bonu\ElasticsearchBuilder\Query\CompositeQuery;
use Bonu\ElasticsearchBuilder\Query\QueryInterface;

/**
 * @internal
 */
final class CompositeQueryTest extends TestCase
{
    #[DependsExternal(TermQueryTest::class, 'itCorrectlyBuildsArray')]
    #[Test]
    public function itBuildsArrayFromAbstractQueryMethod(): void
    {
        $composite = new class() extends CompositeQuery {
            /**
             * @inheritDoc
             */
            #[\Override]
            public function query(): QueryInterface
            {
                return new TermQuery('foo', 'bar');
            }
        };

        $this->assertSame([
            'term' => [
                'foo' => [
                    'value' => 'bar',
                    'boost' => 1.0,
                ],
            ],
        ], $composite->toArray());
    }
}
