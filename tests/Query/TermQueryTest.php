<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Query;

use PHPUnit\Framework\Attributes\Depends;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use Bonu\ElasticsearchBuilder\Query\TermQuery;
use PHPUnit\Framework\Attributes\Test;

final class TermQueryTest extends TestCase
{
    #[Test]
    public function itCorrectlyBuildsArray(): void
    {
        $array = new TermQuery('foo', 'bar')->toArray();

        $this->assertSame([
            'term' => [
                'foo' => [
                    'value' => 'bar',
                    'boost' => 1.0,
                ],
            ],
        ], $array);
    }

    #[Test]
    #[Depends('itCorrectlyBuildsArray')]
    public function itCorrectlySetsBoost(): void
    {
        $array = new TermQuery('foo', 'bar')->boost(10.0)->toArray();

        $this->assertSame(10.0, $array['term']['foo']['boost']);
    }
}
