<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Query;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use Bonu\ElasticsearchBuilder\Query\MatchQuery;
use Bonu\ElasticsearchBuilder\Exception\Query\InvalidOperatorQueryException;

final class MatchQueryTest extends TestCase
{
    #[Test]
    public function itThrowsExceptionIfInvalidOperatorIsProvided(): void
    {
        $this->expectException(InvalidOperatorQueryException::class);

        new MatchQuery('foo', 'bar', 'invalid_operator');
    }

    #[Test]
    public function itCorrectlyBuildsArray(): void
    {
        $array = new MatchQuery('foo', 'bar')->toArray();

        $this->assertSame([
            'match' => [
                'foo' => [
                    'query' => 'bar',
                    'operator' => 'OR',
                    'boost' => 1.0,
                ],
            ],
        ], $array);
    }

    #[Test]
    #[Depends('itCorrectlyBuildsArray')]
    public function itCorrectlySetsBoost(): void
    {
        $array = new MatchQuery('foo', 'bar')->boost(10.0)->toArray();

        $this->assertSame(10.0, $array['match']['foo']['boost']);
    }

    #[Test]
    #[Depends('itCorrectlyBuildsArray')]
    public function itCorrectlySetsOperator(): void
    {
        $array = new MatchQuery('foo', 'bar', MatchQuery::OPERATOR_AND)->toArray();

        $this->assertSame('AND', $array['match']['foo']['operator']);
    }

    #[Test]
    #[Depends('itCorrectlyBuildsArray')]
    public function itCorrectlySetsAnalyzer(): void
    {
        $array = new MatchQuery('foo', 'bar')
            ->analyzer('testing_analyzer')
            ->toArray();

        $this->assertArrayHasKey('analyzer', $array['match']['foo']);
        $this->assertSame('testing_analyzer', $array['match']['foo']['analyzer']);
    }
}
