<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Query;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use Bonu\ElasticsearchBuilder\Query\ExistsQuery;

use const PHP_FLOAT_EPSILON;

/**
 * @internal
 */
final class ExistsQueryTest extends TestCase
{
    #[Test]
    public function itCorrectlyBuildsArray(): void
    {
        $array = new ExistsQuery('user')->toArray();

        $this->assertSame([
            'exists' => [
                'field' => 'user',
                'boost' => 1.0,
            ],
        ], $array);
    }

    #[Depends('itCorrectlyBuildsArray')]
    #[Test]
    public function itCorrectlySetsBoost(): void
    {
        $array = new ExistsQuery('user')->boost(10.0)->toArray();

        $this->assertEqualsWithDelta(10.0, $array['exists']['boost'], PHP_FLOAT_EPSILON);
    }
}
