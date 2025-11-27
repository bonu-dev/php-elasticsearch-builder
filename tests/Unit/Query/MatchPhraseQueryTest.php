<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Query;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use Bonu\ElasticsearchBuilder\Query\MatchPhraseQuery;

final class MatchPhraseQueryTest extends TestCase
{
    #[Test]
    public function itCorrectlyBuildsArray(): void
    {
        $array = new MatchPhraseQuery('foo', 'bar')->toArray();

        $this->assertSame([
            'match_phrase' => [
                'foo' => [
                    'query' => 'bar',
                    'boost' => 1.0,
                ],
            ],
        ], $array);
    }

    #[Test]
    #[Depends('itCorrectlyBuildsArray')]
    public function itCorrectlySetsBoost(): void
    {
        $array = new MatchPhraseQuery('foo', 'bar')->boost(10.0)->toArray();

        $this->assertSame(10.0, $array['match_phrase']['foo']['boost']);
    }

    #[Test]
    #[Depends('itCorrectlyBuildsArray')]
    public function itCorrectlySetsSlop(): void
    {
        $array = new MatchPhraseQuery('foo', 'bar', 10)->toArray();

        $this->assertArrayHasKey('slop', $array['match_phrase']['foo']);
        $this->assertSame(10, $array['match_phrase']['foo']['slop']);
    }

    #[Test]
    #[Depends('itCorrectlyBuildsArray')]
    public function itCorrectlySetsAnalyzer(): void
    {
        $array = new MatchPhraseQuery('foo', 'bar')
            ->analyzer('testing_analyzer')
            ->toArray();

        $this->assertArrayHasKey('analyzer', $array['match_phrase']['foo']);
        $this->assertSame('testing_analyzer', $array['match_phrase']['foo']['analyzer']);
    }
}
