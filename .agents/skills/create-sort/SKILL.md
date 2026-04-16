---
name: create-sort
description: >-
  Creates a new Elasticsearch Sort class in this project. Use when asked to add, implement,
  or create a new sort type (e.g. "add GeoDistanceSort", "implement ScriptSort", "create a new
  sort for nested fields"). Handles class creation, unit tests, and all project conventions
  (immutability, PHPDoc, no final/private).
compatibility: Requires PHP 8.4+, PHPUnit, PHPStan.
metadata:
  author: bonu-dev
  version: "1.0"
---

# Create a New Elasticsearch Sort

Step-by-step guide for adding a new Sort class to this project. Every convention below is enforced by CI (PHPStan, php-cs-fixer, Rector, PHPUnit).

## 1. Identify the Elasticsearch Sort Type

Before writing code, determine:

- **Elasticsearch sort type** from the [Elasticsearch Sort docs](https://www.elastic.co/docs/reference/elasticsearch/rest-apis/sort-search-results).
- **Parameters** the sort accepts (field, direction, mode, nested path, distance type, etc.).
- **Does it use a field name?** If yes, accept `string|\Stringable $field`.
- **Does it need direction?** If yes, use `SortDirectionEnum` (ASC/DESC).
- **Optional parameters?** Use nullable constructor params + `array_filter` in `toArray()` to exclude nulls.
- **Can it compose `FieldSort`?** `ScoreSort` delegates to `FieldSort('_score', ...)` — consider if composition fits.

Sort classes in this project are **fully immutable by construction** — they have no mutation methods, only constructor params. All configuration is set at instantiation time.

## 2. Create the Sort Class

**File**: `src/Sort/{SortName}Sort.php`

**Namespace**: `Bonu\ElasticsearchBuilder\Sort`

### Template (field-based sort with direction and optional params)

Reference: `FieldSort.php`

```php
<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Sort;

use function array_filter;

/**
 * @see https://www.elastic.co/docs/reference/elasticsearch/rest-apis/sort-search-results
 */
class {SortName}Sort implements SortInterface
{
    /**
     * @param string|\Stringable $field
     * @param \Bonu\ElasticsearchBuilder\Sort\SortDirectionEnum $direction
     * @param null|string $mode
     */
    public function __construct(
        protected string | \Stringable $field,
        protected SortDirectionEnum $direction = SortDirectionEnum::ASC,
        protected ?string $mode = null,
    ) {
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function toArray(): array
    {
        return [
            (string) $this->field => array_filter([
                'order' => $this->direction->value,
                'mode' => $this->mode,
            ], static fn (mixed $value): bool => $value !== null),
        ];
    }
}
```

### Template (sort that delegates to FieldSort)

Reference: `ScoreSort.php`

```php
<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Sort;

/**
 * @see https://www.elastic.co/docs/reference/elasticsearch/rest-apis/sort-search-results
 */
class {SortName}Sort implements SortInterface
{
    /**
     * @param \Bonu\ElasticsearchBuilder\Sort\SortDirectionEnum $direction
     */
    public function __construct(
        protected SortDirectionEnum $direction = SortDirectionEnum::DESC,
    ) {
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function toArray(): array
    {
        return new FieldSort('_score', $this->direction)->toArray();
    }
}
```

### Template (complex sort with nested object structure)

For sorts like `_geo_distance` that produce a different array structure:

```php
<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Sort;

use function array_filter;

/**
 * @see https://www.elastic.co/docs/reference/elasticsearch/rest-apis/sort-search-results
 */
class {SortName}Sort implements SortInterface
{
    /**
     * @param string|\Stringable $field
     * @param float $lat
     * @param float $lon
     * @param \Bonu\ElasticsearchBuilder\Sort\SortDirectionEnum $direction
     * @param null|string $unit
     * @param null|string $mode
     * @param null|string $distanceType
     */
    public function __construct(
        protected string | \Stringable $field,
        protected float $lat,
        protected float $lon,
        protected SortDirectionEnum $direction = SortDirectionEnum::ASC,
        protected ?string $unit = null,
        protected ?string $mode = null,
        protected ?string $distanceType = null,
    ) {
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function toArray(): array
    {
        return [
            '_geo_distance' => array_filter([
                (string) $this->field => ['lat' => $this->lat, 'lon' => $this->lon],
                'order' => $this->direction->value,
                'unit' => $this->unit,
                'mode' => $this->mode,
                'distance_type' => $this->distanceType,
            ], static fn (mixed $value): bool => $value !== null),
        ];
    }
}
```

## 3. Create Unit Tests

**File**: `tests/Unit/Sort/{SortName}SortTest.php`

**Namespace**: `Bonu\ElasticsearchBuilder\Tests\Unit\Sort`

### Template (field-based sort with optional params)

Reference: `FieldSortTest.php`

```php
<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Sort;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use Bonu\ElasticsearchBuilder\Sort\{SortName}Sort;
use Bonu\ElasticsearchBuilder\Sort\SortDirectionEnum;

/**
 * @internal
 */
final class {SortName}SortTest extends TestCase
{
    #[Test]
    public function itCorrectlyBuildsArray(): void
    {
        $sort = new {SortName}Sort('my_field', SortDirectionEnum::DESC, 'some_option');

        $this->assertSame([
            'my_field' => [
                'order' => 'desc',
                'mode' => 'some_option',
            ],
        ], $sort->toArray());
    }

    #[Depends('itCorrectlyBuildsArray')]
    #[Test]
    public function itDoesNotIncludeOptionalParamIfNull(): void
    {
        $sort = new {SortName}Sort('my_field', SortDirectionEnum::ASC);

        $this->assertArrayNotHasKey('mode', $sort->toArray()['my_field']);
    }
}
```

### Template (delegating sort)

Reference: `ScoreSortTest.php`

```php
<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Sort;

use PHPUnit\Framework\Attributes\Test;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use Bonu\ElasticsearchBuilder\Sort\{SortName}Sort;
use Bonu\ElasticsearchBuilder\Sort\SortDirectionEnum;

/**
 * @internal
 */
final class {SortName}SortTest extends TestCase
{
    #[Test]
    public function itCorrectlyBuildsArray(): void
    {
        $sort = new {SortName}Sort(SortDirectionEnum::DESC);

        $this->assertSame([
            '_score' => [
                'order' => 'desc',
            ],
        ], $sort->toArray());
    }
}
```

### Test patterns to follow

- **Basic build test**: Instantiate with all params, assert exact `toArray()` with `assertSame`.
- **Optional param exclusion test**: `#[Depends('itCorrectlyBuildsArray')]`, instantiate without optional, `assertArrayNotHasKey`.
- **Direction test** (optional): Test both `ASC` and `DESC`.
- **Default direction test** (optional): Instantiate with defaults, verify expected direction.

## Hard Rules (CI will reject violations)

- **No `final`** on classes, methods, or constants under `src/`.
- **No `private`** visibility under `src/`. Use `public` or `protected`.
- **`@see` link required** — class-level PHPDoc must have `@see https://www.elastic.co/docs/reference/elasticsearch/rest-apis/sort-search-results` linking to the Elasticsearch sort docs page.
- **PHPDoc on everything** — every method and property must have a docblock.
  - Methods: `@param`, `@return`, `@throws` (or `@inheritDoc` if parent defines them).
  - Properties: `@var` with FQCN.
  - All type references in PHPDoc use FQCNs (e.g. `\Bonu\ElasticsearchBuilder\Sort\SortDirectionEnum`).
- **`declare(strict_types=1)`** in every file.
- **Immutability** — sort classes are immutable by construction (no setters). If adding a mutation method, `clone $this` before modifying.
- **Class element order**: `use` traits, constants, properties, constructor, public methods, protected methods.
- **Import order**: sorted by length, grouped (class, function, const).
- **No Yoda style**: `$var === true`, not `true === $var`.
- **Native function invocation**: `\array_filter()` (backslash-prefixed).
- **PHPDoc tag order**: `@inheritDoc` > `@test` > `@dataProvider` > `@template` > `@param` > `@return` > `@uses` > `@throws`.
- **PHPUnit tests**: `#[Test]` attribute, camelCase method names, `/** @internal */` on test class, `final` test class.

## Available Components

| Component | Purpose |
|-----------|---------|
| `SortInterface` | Contract: `toArray(): array` — all sorts implement this |
| `SortDirectionEnum` | PHP enum with `ASC = 'asc'` and `DESC = 'desc'` cases |
| `FieldSort` | Reference implementation — field + direction + optional format |
| `ScoreSort` | Composition example — delegates to `FieldSort('_score', ...)` |

## QueryBuilder Integration

No special registration is needed. The `QueryBuilder::sort()` method accepts any `SortInterface`:

```php
$builder = new QueryBuilder('index')
    ->sort(new {SortName}Sort('field', SortDirectionEnum::ASC));
```

The builder clones itself, appends the sort to its `$sorts` array, and in `build()` maps all sorts to `toArray()` under `body['sort']`.

## Reference Files

- `src/Sort/SortInterface.php` — interface contract
- `src/Sort/SortDirectionEnum.php` — direction enum (ASC/DESC)
- `src/Sort/FieldSort.php` — reference implementation (field-based sort)
- `src/Sort/ScoreSort.php` — composition pattern (delegates to FieldSort)
- `src/QueryBuilder.php` — builder integration (`sort()` method)
- `tests/Unit/Sort/FieldSortTest.php` — reference test (with optional param exclusion)
- `tests/Unit/Sort/ScoreSortTest.php` — reference test (delegating sort)
- `tests/TestCase.php` — base test class

## Verification

After creating all files, run:

```bash
composer code:analyse    # PHPStan — must pass with 0 errors
composer code:fix        # Rector + php-cs-fixer — apply auto-fixes
vendor/bin/phpunit --testsuite unit --filter {SortName}SortTest  # Tests must pass
```
