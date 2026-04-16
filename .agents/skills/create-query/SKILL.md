---
name: create-query
description: >-
  Creates a new Elasticsearch Query class in this project. Use when asked to add, implement,
  or create a new query type (e.g. "add FuzzyQuery", "implement WildcardQuery", "create a new
  query for prefix matching"). Handles class creation, exception creation, unit tests, and
  all project conventions (immutability, PHPDoc, traits, no final/private).
compatibility: Requires PHP 8.4+, PHPUnit, PHPStan.
metadata:
  author: bonu-dev
  version: "1.0"
---

# Create a New Elasticsearch Query

Step-by-step guide for adding a new Query class to this project. Every convention below is enforced by CI (PHPStan, php-cs-fixer, Rector, PHPUnit).

## 1. Identify the Elasticsearch Query Type

Before writing code, determine:

- **Elasticsearch query name** (e.g. `fuzzy`, `wildcard`, `prefix`) from the [Elasticsearch Query DSL docs](https://www.elastic.co/docs/reference/query-languages/query-dsl).
- **Parameters** the query accepts (field, value, options like `fuzziness`, `rewrite`, etc.).
- **Does it support boost?** Most queries do. If yes, use the `BoostableQuery` trait.
- **Does it support analyzer?** Full-text queries typically do. If yes, use the `AnalyzerAwareQuery` trait.
- **Does it need input validation?** If constructor params have constraints (e.g. enum values, non-empty), create a custom exception.

## 2. Create the Query Class

**File**: `src/Query/{QueryName}Query.php`

**Namespace**: `Bonu\ElasticsearchBuilder\Query`

### Template (simple query with field + value, boostable)

```php
<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Query;

/**
 * @see https://www.elastic.co/docs/reference/query-languages/query-dsl/query-dsl-{query-name}-query
 */
class {QueryName}Query implements QueryInterface
{
    use BoostableQuery;

    /**
     * @param string|\Stringable $field
     * @param string $value
     */
    public function __construct(
        protected string | \Stringable $field,
        protected string $value,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            '{query_name}' => [
                (string) $this->field => $this->addBoostToQuery([
                    'value' => $this->value,
                ]),
            ],
        ];
    }
}
```

### Template (full-text query with field + value, boostable + analyzer-aware)

```php
<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Query;

/**
 * @see https://www.elastic.co/docs/reference/query-languages/query-dsl/query-dsl-{query-name}-query
 */
class {QueryName}Query implements QueryInterface
{
    use BoostableQuery;
    use AnalyzerAwareQuery;

    /**
     * @param string|\Stringable $field
     * @param bool|float|int|string $value
     */
    public function __construct(
        protected string | \Stringable $field,
        protected int | float | string | bool $value,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $value = [
            'query' => $this->value,
        ];
        $value = $this->addBoostToQuery($value);
        $value = $this->addAnalyzerToQuery($value);

        return [
            '{query_name}' => [
                (string) $this->field => $value,
            ],
        ];
    }
}
```

### Template (query with constructor validation)

```php
<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Query;

use Bonu\ElasticsearchBuilder\Exception\Query\Invalid{Param}QueryException;

use function implode;
use function sprintf;
use function in_array;

/**
 * @see https://www.elastic.co/docs/reference/query-languages/query-dsl/query-dsl-{query-name}-query
 */
class {QueryName}Query implements QueryInterface
{
    use BoostableQuery;

    public const string OPTION_A = 'a';
    public const string OPTION_B = 'b';

    /**
     * @param string|\Stringable $field
     * @param string $value
     * @param self::OPTION_* $option
     *
     * @throws \Bonu\ElasticsearchBuilder\Exception\Query\Invalid{Param}QueryException
     */
    public function __construct(
        protected string | \Stringable $field,
        protected string $value,
        protected string $option = self::OPTION_A,
    ) {
        if (! in_array($option, [self::OPTION_A, self::OPTION_B], true)) {
            throw new Invalid{Param}QueryException(sprintf(
                'Invalid option for {query_name} query. Given "%s", expected one of [%s].',
                $option,
                implode(', ', [self::OPTION_A, self::OPTION_B]),
            ));
        }
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            '{query_name}' => [
                (string) $this->field => $this->addBoostToQuery([
                    'value' => $this->value,
                    'option' => $this->option,
                ]),
            ],
        ];
    }
}
```

### Template (query with optional mutation method — immutable clone)

If the query has an optional parameter set via a fluent method (not constructor):

```php
/**
 * @var null|int
 */
protected ?int $slop = null;

/**
 * @param int $slop
 *
 * @return static
 */
public function slop(int $slop): static
{
    $clone = clone $this;
    $clone->slop = $slop;

    return $clone;
}
```

In `toArray()`, conditionally include the value:

```php
$value = [
    'query' => $this->value,
];

if ($this->slop !== null) {
    $value['slop'] = $this->slop;
}

$value = $this->addBoostToQuery($value);
```

## 3. Create Custom Exceptions (if needed)

**File**: `src/Exception/Query/{ExceptionName}Exception.php`

**Namespace**: `Bonu\ElasticsearchBuilder\Exception\Query`

```php
<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Exception\Query;

class {ExceptionName}Exception extends QueryException
{
}
```

Exceptions are always:
- In `src/Exception/Query/`
- Extend `QueryException` (which extends `\RuntimeException`)
- Empty body (message passed at throw site)
- No `final`, no `private`

## 4. Create Unit Tests

**File**: `tests/Unit/Query/{QueryName}QueryTest.php`

**Namespace**: `Bonu\ElasticsearchBuilder\Tests\Unit\Query`

### Template

```php
<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Query;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use Bonu\ElasticsearchBuilder\Query\{QueryName}Query;

use const PHP_FLOAT_EPSILON;

/**
 * @internal
 */
final class {QueryName}QueryTest extends TestCase
{
    #[Test]
    public function itCorrectlyBuildsArray(): void
    {
        $array = new {QueryName}Query('foo', 'bar')->toArray();

        $this->assertSame([
            '{query_name}' => [
                'foo' => [
                    'value' => 'bar',
                    'boost' => 1.0,
                ],
            ],
        ], $array);
    }

    #[Depends('itCorrectlyBuildsArray')]
    #[Test]
    public function itCorrectlySetsBoost(): void
    {
        $array = new {QueryName}Query('foo', 'bar')->boost(10.0)->toArray();

        $this->assertEqualsWithDelta(10.0, $array['{query_name}']['foo']['boost'], PHP_FLOAT_EPSILON);
    }
}
```

### Test patterns to follow

- **Basic build test**: Instantiate with required params, assert exact `toArray()` output with `assertSame`.
- **Boost test**: `#[Depends('itCorrectlyBuildsArray')]`, set boost, assert with `assertEqualsWithDelta`.
- **Analyzer test** (if trait used): `#[Depends('itCorrectlyBuildsArray')]`, set analyzer, `assertArrayHasKey` + `assertSame`.
- **Constructor validation test**: `expectException(SpecificException::class)` before invalid construction.
- **Optional param test**: `#[Depends('itCorrectlyBuildsArray')]`, test with and without optional values.
- **Immutability test** (optional): Verify original instance unchanged after mutation.

### Test for constructor validation

```php
#[Test]
public function itThrowsExceptionIfInvalidOptionIsProvided(): void
{
    $this->expectException(Invalid{Param}QueryException::class);

    new {QueryName}Query('foo', 'bar', 'invalid_value');
}
```

### Test for analyzer (if AnalyzerAwareQuery trait used)

```php
#[Depends('itCorrectlyBuildsArray')]
#[Test]
public function itCorrectlySetsAnalyzer(): void
{
    $array = new {QueryName}Query('foo', 'bar')
        ->analyzer('testing_analyzer')
        ->toArray();

    $this->assertArrayHasKey('analyzer', $array['{query_name}']['foo']);
    $this->assertSame('testing_analyzer', $array['{query_name}']['foo']['analyzer']);
}
```

## 5. Update src/Query/AGENTS.md

Add the new query to the FILES table in `src/Query/AGENTS.md`:

```markdown
| `{QueryName}Query.php` | {Brief description} | `BoostableQuery` |
```

## Hard Rules (CI will reject violations)

- **No `final`** on classes, methods, or constants under `src/`.
- **No `private`** visibility under `src/`. Use `public` or `protected`.
- **`@see` link required** — class-level PHPDoc must have `@see https://www.elastic.co/docs/reference/query-languages/query-dsl/query-dsl-{query-name}-query` linking to the exact Elasticsearch docs page (not a generic page).
- **PHPDoc on everything** — every method and property must have a docblock.
  - Methods: `@param`, `@return`, `@throws` (or `@inheritDoc` if parent defines them).
  - Properties: `@var` with FQCN.
  - All type references in PHPDoc use FQCNs (e.g. `\Bonu\ElasticsearchBuilder\Query\QueryInterface`).
- **`declare(strict_types=1)`** in every file.
- **Immutability** — every mutation method must `clone $this` before modifying.
- **Class element order**: `use` traits, constants, properties, constructor, public methods, protected methods.
- **Import order**: sorted by length, grouped (class, function, const).
- **No Yoda style**: `$var === true`, not `true === $var`.
- **Native function invocation**: `\count()`, `\array_map()`, `\in_array()` (backslash-prefixed).
- **PHPDoc tag order**: `@inheritDoc` > `@test` > `@dataProvider` > `@template` > `@param` > `@return` > `@uses` > `@throws`.
- **PHPUnit tests**: `#[Test]` attribute, camelCase method names, `/** @internal */` on test class, `final` test class.

## Available Traits

| Trait | What it adds | When to use |
|-------|-------------|-------------|
| `BoostableQuery` | `boost(float): static` + `addBoostToQuery(array): array` | Query supports `boost` parameter |
| `AnalyzerAwareQuery` | `analyzer(string): static` + `addAnalyzerToQuery(array): array` | Full-text query supporting custom analyzer |

## Available Base Classes

| Class | Purpose | When to use |
|-------|---------|-------------|
| `QueryInterface` | Contract for all queries | Always implement this |
| `CompositeQuery` | Reusable query combinations | User-defined domain composites |
| `RangeQuery` | Abstract base for range queries | Extend for new range types (numeric, date, IP, etc.) |

## Reference Files

- `src/Query/QueryInterface.php` — interface contract
- `src/Query/BoostableQuery.php` — boost trait
- `src/Query/AnalyzerAwareQuery.php` — analyzer trait
- `src/Query/TermQuery.php` — simplest concrete query (reference implementation)
- `src/Query/MatchQuery.php` — query with validation + two traits
- `src/Query/MatchPhraseQuery.php` — query with optional fluent param (slop)
- `src/Query/CompositeQuery.php` — abstract for reusable composites
- `src/Query/RangeQuery.php` — abstract base for range queries
- `src/Exception/Query/QueryException.php` — abstract exception base
- `tests/Unit/Query/TermQueryTest.php` — simplest test (reference)
- `tests/Unit/Query/MatchQueryTest.php` — test with validation + traits
- `tests/TestCase.php` — base test class
- `tests/Fixture/BoolQueryFixture.php` — test fixture for queries

## Verification

After creating all files, run:

```bash
composer code:analyse    # PHPStan — must pass with 0 errors
composer code:fix        # Rector + php-cs-fixer — apply auto-fixes
vendor/bin/phpunit --testsuite unit --filter {QueryName}QueryTest  # Tests must pass
```
