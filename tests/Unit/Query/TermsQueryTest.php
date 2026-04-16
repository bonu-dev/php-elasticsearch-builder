<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Query;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use Bonu\ElasticsearchBuilder\Query\TermsQuery;
use Bonu\ElasticsearchBuilder\Exception\Query\EmptyTermsQueryException;

use const PHP_FLOAT_EPSILON;

/**
 * @internal
 */
final class TermsQueryTest extends TestCase
{
    #[Test]
    public function itCorrectlyBuildsArray(): void
    {
        $array = new TermsQuery('foo', ['bar', 'baz'])->toArray();

        $this->assertSame([
            'terms' => [
                'foo' => ['bar', 'baz'],
                'boost' => 1.0,
            ],
        ], $array);
    }

    #[Depends('itCorrectlyBuildsArray')]
    #[Test]
    public function itCorrectlySetsBoost(): void
    {
        $array = new TermsQuery('foo', ['bar', 'baz'])->boost(10.0)->toArray();

        $this->assertEqualsWithDelta(10.0, $array['terms']['boost'], PHP_FLOAT_EPSILON);
    }

    #[Test]
    public function itThrowsExceptionIfEmptyValuesAreProvided(): void
    {
        $this->expectException(EmptyTermsQueryException::class);

        new TermsQuery('foo', []);
    }

    #[Depends('itCorrectlyBuildsArray')]
    #[Test]
    public function itCorrectlyHandlesIntegerValues(): void
    {
        $array = new TermsQuery('status', [1, 2, 3])->toArray();

        $this->assertSame([1, 2, 3], $array['terms']['status']);
    }

    #[Depends('itCorrectlyBuildsArray')]
    #[Test]
    public function itCorrectlyHandlesMixedValueTypes(): void
    {
        $array = new TermsQuery('field', ['text', 42, 3.14, true])->toArray();

        $this->assertSame(['text', 42, 3.14, true], $array['terms']['field']);
    }

    #[Depends('itCorrectlyBuildsArray')]
    #[Test]
    public function itCorrectlyHandlesSingleValue(): void
    {
        $array = new TermsQuery('foo', ['bar'])->toArray();

        $this->assertSame([
            'terms' => [
                'foo' => ['bar'],
                'boost' => 1.0,
            ],
        ], $array);
    }
}
