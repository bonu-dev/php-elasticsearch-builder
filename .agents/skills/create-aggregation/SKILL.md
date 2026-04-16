---
name: create-aggregation
description: >-
  Creates a new Elasticsearch Aggregation class in this project. Use when asked to add, implement,
  or create a new aggregation type (e.g. "add CardinalityAggregation", "implement DateHistogramAggregation",
  "create a new aggregation for average values"). Handles class creation, exception creation, unit tests,
  trait tests, and all project conventions (immutability, PHPDoc, traits, no final/private).
compatibility: Requires PHP 8.4+, PHPUnit, PHPStan.
metadata:
  author: bonu-dev
  version: "1.0"
---

# Create a New Elasticsearch Aggregation

Step-by-step guide for adding a new Aggregation class to this project. Every convention below is enforced by CI (PHPStan, php-cs-fixer, Rector, PHPUnit).

## 1. Identify the Elasticsearch Aggregation Type

Before writing code, determine:

- **Elasticsearch aggregation name** (e.g. `cardinality`, `avg`, `date_histogram`) from the [Elasticsearch Aggregation docs](https://www.elastic.co/docs/reference/elasticsearch/aggregation).
- **Aggregation category**: bucket (groups documents) or metric (computes values).
- **Parameters** the aggregation accepts (field, interval, options).
- **Does it support filter context?** Most aggregations do. If yes, use the `FilterableAggregation` trait.
- **Can it be made global?** Most aggregations can. If yes, use the `GlobalizableAggregation` trait.
- **Does it support size limits?** Bucket aggregations typically do. If yes, use the `SizeableAggregation` trait.
- **Does it support sub-aggregations?** Bucket aggregations may. If yes, add an `aggregation()` method with duplicate checking.
- **Does it need input validation?** If constructor params have constraints, create a custom exception.

## 2. Create the Aggregation Class

**File**: `src/Aggregation/{AggregationName}Aggregation.php`

**Namespace**: `Bonu\ElasticsearchBuilder\Aggregation`

### Template (simple metrics aggregation — field-based, filterable, globalizable)

Reference: `StatsAggregation.php`

```php
<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Aggregation;

/**
 * @see https://www.elastic.co/docs/reference/aggregations/search-aggregations-{category}-{aggregation-name}-aggregation
 */
class {AggregationName}Aggregation implements AggregationInterface
{
    use FilterableAggregation;
    use GlobalizableAggregation;

    /**
     * @param string|\Stringable $name
     * @param string|\Stringable $field
     */
    public function __construct(
        protected string | \Stringable $name,
        protected string | \Stringable $field,
    ) {
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getName(): string
    {
        return (string) $this->name;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function toArray(): array
    {
        $value = ['{aggregation_name}' => ['field' => (string) $this->field]];
        $value = $this->addFilterToAggregation($value, $this->getName());
        $value = $this->addGlobalToAggregation($value, $this->getName());

        return [
            $this->getName() => $value,
        ];
    }
}
```

### Template (bucket aggregation — field-based, filterable, globalizable, sizeable)

Reference: `TermsAggregation.php`

```php
<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Aggregation;

/**
 * @see https://www.elastic.co/docs/reference/aggregations/search-aggregations-bucket-{aggregation-name}-aggregation
 */
class {AggregationName}Aggregation implements AggregationInterface
{
    use SizeableAggregation;
    use FilterableAggregation;
    use GlobalizableAggregation;

    /**
     * @param string|\Stringable $name
     * @param string|\Stringable $field
     */
    public function __construct(
        protected string | \Stringable $name,
        protected string | \Stringable $field,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return (string) $this->name;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $value = ['field' => (string) $this->field];
        $value = $this->addSizeToAggregation($value);
        $value = ['{aggregation_name}' => $value];
        $value = $this->addFilterToAggregation($value, $this->getName());
        $value = $this->addGlobalToAggregation($value, $this->getName());

        return [
            $this->getName() => $value,
        ];
    }
}
```

### Template (aggregation with constructor validation)

Reference: `HistogramAggregation.php`

```php
<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Aggregation;

use Bonu\ElasticsearchBuilder\Exception\Aggregation\Invalid{Param}Exception;

use function array_filter;

/**
 * @see https://www.elastic.co/docs/reference/aggregations/search-aggregations-bucket-{aggregation-name}-aggregation
 */
class {AggregationName}Aggregation implements AggregationInterface
{
    use FilterableAggregation;
    use GlobalizableAggregation;

    /**
     * @param string|\Stringable $name
     * @param string|\Stringable $field
     * @param int|float $interval
     * @param null|int $minDocCount
     *
     * @throws \Bonu\ElasticsearchBuilder\Exception\Aggregation\Invalid{Param}Exception
     */
    public function __construct(
        protected string | \Stringable $name,
        protected string | \Stringable $field,
        protected int | float $interval,
        protected ?int $minDocCount = null,
    ) {
        if ($interval <= 0) {
            throw new Invalid{Param}Exception('Interval must be greater than 0.');
        }
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getName(): string
    {
        return (string) $this->name;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function toArray(): array
    {
        $value = array_filter([
            'field' => (string) $this->field,
            'interval' => $this->interval,
            'min_doc_count' => $this->minDocCount,
        ], static fn (mixed $v): bool => $v !== null);
        $value = ['{aggregation_name}' => $value];
        $value = $this->addFilterToAggregation($value, $this->getName());
        $value = $this->addGlobalToAggregation($value, $this->getName());

        return [
            $this->getName() => $value,
        ];
    }
}
```

### Template (aggregation with sub-aggregations)

Reference: `NestedAggregation.php`

```php
<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Aggregation;

use Bonu\ElasticsearchBuilder\Exception\Aggregation\Duplicated{AggName}AggregationException;

use function array_key_exists;
use function iterator_to_array;

/**
 * @see https://www.elastic.co/docs/reference/aggregations/search-aggregations-bucket-{aggregation-name}-aggregation
 */
class {AggregationName}Aggregation implements AggregationInterface
{
    use FilterableAggregation;
    use GlobalizableAggregation;

    /**
     * @var array<string, \Bonu\ElasticsearchBuilder\Aggregation\AggregationInterface>
     */
    protected array $aggregations = [];

    /**
     * @param string|\Stringable $name
     * @param string|\Stringable $path
     */
    public function __construct(
        protected string | \Stringable $name,
        protected string | \Stringable $path,
    ) {
    }

    /**
     * @param \Bonu\ElasticsearchBuilder\Aggregation\AggregationInterface $aggregation
     *
     * @return static
     *
     * @throws \Bonu\ElasticsearchBuilder\Exception\Aggregation\Duplicated{AggName}AggregationException
     */
    public function aggregation(AggregationInterface $aggregation): static
    {
        if (array_key_exists($aggregation->getName(), $this->aggregations)) {
            throw new Duplicated{AggName}AggregationException(
                'Aggregation with name "' . $aggregation->getName() . '" already exists.',
            );
        }

        $clone = clone $this;
        $clone->aggregations[$aggregation->getName()] = $aggregation;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getName(): string
    {
        return (string) $this->name;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function toArray(): array
    {
        $value = [
            '{aggregation_name}' => ['path' => (string) $this->path],
            'aggs' => iterator_to_array($this->mapAggregations()),
        ];
        $value = $this->addFilterToAggregation($value, $this->getName());
        $value = $this->addGlobalToAggregation($value, $this->getName());

        return [
            $this->getName() => $value,
        ];
    }

    /**
     * @return \Generator<string, array<string, mixed>>
     */
    protected function mapAggregations(): \Generator
    {
        foreach ($this->aggregations as $aggregation) {
            yield from $aggregation->toArray();
        }
    }
}
```

## 3. Create Custom Exceptions (if needed)

**File**: `src/Exception/Aggregation/{ExceptionName}Exception.php`

**Namespace**: `Bonu\ElasticsearchBuilder\Exception\Aggregation`

```php
<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Exception\Aggregation;

class {ExceptionName}Exception extends AggregationException
{
}
```

Exceptions are always:
- In `src/Exception/Aggregation/`
- Extend `AggregationException` (which extends `\RuntimeException`)
- Empty body (message passed at throw site)
- No `final`, no `private`

## 4. Create Unit Tests

**File**: `tests/Unit/Aggregation/{AggregationName}AggregationTest.php`

**Namespace**: `Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation`

### Template (metrics aggregation — filterable + globalizable, no size)

Reference: `StatsAggregationTest.php`

```php
<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;
use Bonu\ElasticsearchBuilder\Tests\TestCase;
use PHPUnit\Framework\Attributes\DependsExternal;
use Bonu\ElasticsearchBuilder\Aggregation\{AggregationName}Aggregation;
use Bonu\ElasticsearchBuilder\Tests\Fixture\BoolQueryFixture;
use Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation\Trait\FilterableAggregationTest;
use Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation\Trait\GlobalizableAggregationTest;

/**
 * @internal
 */
final class {AggregationName}AggregationTest extends TestCase
{
    #[Test]
    public function itBuildsBasic{AggregationName}Aggregation(): void
    {
        $agg = new {AggregationName}Aggregation('my_agg', 'my_field');

        $this->assertSame([
            'my_agg' => [
                '{aggregation_name}' => [
                    'field' => 'my_field',
                ],
            ],
        ], $agg->toArray());
    }

    #[Depends('itBuildsBasic{AggregationName}Aggregation')]
    #[DependsExternal(GlobalizableAggregationTest::class, 'itAddsGlobalToAggregation')]
    #[Test]
    public function itCanBeGlobal(): void
    {
        $agg = new {AggregationName}Aggregation('my_agg', 'my_field');
        $agg = $agg->asGlobal();

        $this->assertEquals([
            'my_agg' => [
                'global' => (object) [],
                'aggs' => [
                    'my_agg' => [
                        '{aggregation_name}' => [
                            'field' => 'my_field',
                        ],
                    ],
                ],
            ],
        ], $agg->toArray());
    }

    #[Depends('itBuildsBasic{AggregationName}Aggregation')]
    #[DependsExternal(FilterableAggregationTest::class, 'itAddsFilterToAggregation')]
    #[Test]
    public function itCanBeFiltered(): void
    {
        $agg = new {AggregationName}Aggregation('my_agg', 'my_field');
        $agg = $agg->query(new BoolQueryFixture('foo'));

        $this->assertSame([
            'my_agg' => [
                'filter' => [
                    'foo' => 'fixture_for_bool_query',
                ],
                'aggs' => [
                    'my_agg' => [
                        '{aggregation_name}' => [
                            'field' => 'my_field',
                        ],
                    ],
                ],
            ],
        ], $agg->toArray());
    }

    #[Depends('itBuildsBasic{AggregationName}Aggregation')]
    #[Depends('itCanBeFiltered')]
    #[Depends('itCanBeGlobal')]
    #[Test]
    public function itCanBeGlobalAndFilteredTogether(): void
    {
        $agg = new {AggregationName}Aggregation('my_agg', 'my_field');
        $agg = $agg->asGlobal()->query(new BoolQueryFixture('foo'));

        $this->assertEquals([
            'my_agg' => [
                'global' => (object) [],
                'aggs' => [
                    'my_agg' => [
                        'filter' => [
                            'foo' => 'fixture_for_bool_query',
                        ],
                        'aggs' => [
                            'my_agg' => [
                                '{aggregation_name}' => [
                                    'field' => 'my_field',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], $agg->toArray());
    }
}
```

### Template (bucket aggregation — filterable + globalizable + sizeable)

Reference: `TermsAggregationTest.php`

Add these additional tests beyond the metrics template:

```php
use Bonu\ElasticsearchBuilder\Tests\Unit\Aggregation\Trait\SizeableAggregationTest;

// Add this DependsExternal import and test:

#[Depends('itBuildsBasic{AggregationName}Aggregation')]
#[DependsExternal(SizeableAggregationTest::class, 'itAddsSizeToAggregation')]
#[Test]
public function itAddsSizeWhenProvided(): void
{
    $agg = new {AggregationName}Aggregation('my_agg', 'my_field');
    $agg = $agg->size(20);

    $this->assertSame([
        'my_agg' => [
            '{aggregation_name}' => [
                'field' => 'my_field',
                'size' => 20,
            ],
        ],
    ], $agg->toArray());
}

// Update the combined test to include size:

#[Depends('itBuildsBasic{AggregationName}Aggregation')]
#[Depends('itAddsSizeWhenProvided')]
#[Depends('itCanBeFiltered')]
#[Depends('itCanBeGlobal')]
#[Test]
public function itCanBeGlobalAndFilteredAndSizedTogether(): void
{
    $agg = new {AggregationName}Aggregation('my_agg', 'my_field');
    $agg = $agg->size(5)->asGlobal()->query(new BoolQueryFixture('foo'));

    $this->assertEquals([
        'my_agg' => [
            'global' => (object) [],
            'aggs' => [
                'my_agg' => [
                    'filter' => [
                        'foo' => 'fixture_for_bool_query',
                    ],
                    'aggs' => [
                        'my_agg' => [
                            '{aggregation_name}' => [
                                'field' => 'my_field',
                                'size' => 5,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ], $agg->toArray());
}
```

### Test for constructor validation

```php
#[Test]
public function itThrowsExceptionIfInvalid{Param}IsProvided(): void
{
    $this->expectException(Invalid{Param}Exception::class);

    new {AggregationName}Aggregation('my_agg', 'my_field', 0);
}
```

### Test for sub-aggregation duplicate detection

```php
#[Test]
public function itThrowsExceptionOnDuplicateSubAggregation(): void
{
    $this->expectException(Duplicated{AggName}AggregationException::class);

    $subAgg = new TermsAggregation('sub', 'field');
    new {AggregationName}Aggregation('my_agg', 'path')
        ->aggregation($subAgg)
        ->aggregation($subAgg);
}
```

### Test patterns to follow

- **Basic build test**: Instantiate, assert exact `toArray()` with `assertSame`.
- **Global test**: `#[DependsExternal(GlobalizableAggregationTest::class, 'itAddsGlobalToAggregation')]`, use `assertEquals` (because `(object) []`).
- **Filter test**: `#[DependsExternal(FilterableAggregationTest::class, 'itAddsFilterToAggregation')]`, use `BoolQueryFixture`, `assertSame`.
- **Size test**: `#[DependsExternal(SizeableAggregationTest::class, 'itAddsSizeToAggregation')]`, `assertSame`.
- **Combined test**: All traits together, `assertEquals` (because global uses `(object) []`).
- **Constructor validation test**: `expectException`, invalid params.
- **Duplicate sub-aggregation test**: If aggregation supports sub-aggregations.

**Important**: Use `assertEquals` (not `assertSame`) whenever the expected array contains `(object) []` (global aggregation wrapping), because `assertSame` checks identity for objects.

## 5. Update src/Aggregation/AGENTS.md

Add the new aggregation to the FILES table in `src/Aggregation/AGENTS.md`:

```markdown
| `{AggregationName}Aggregation.php` | {Brief description} | `FilterableAggregation`, `GlobalizableAggregation` |
```

## Hard Rules (CI will reject violations)

- **No `final`** on classes, methods, or constants under `src/`.
- **No `private`** visibility under `src/`. Use `public` or `protected`.
- **`@see` link required** — class-level PHPDoc must have `@see https://www.elastic.co/docs/reference/aggregations/search-aggregations-{category}-{aggregation-name}-aggregation` linking to the exact Elasticsearch docs page (not a generic page).
- **PHPDoc on everything** — every method and property must have a docblock.
  - Methods: `@param`, `@return`, `@throws` (or `@inheritDoc` if parent defines them).
  - Properties: `@var` with FQCN.
  - All type references in PHPDoc use FQCNs (e.g. `\Bonu\ElasticsearchBuilder\Aggregation\AggregationInterface`).
- **`declare(strict_types=1)`** in every file.
- **Immutability** — every mutation method must `clone $this` before modifying.
- **Class element order**: `use` traits, constants, properties, constructor, public methods, protected methods.
- **Import order**: sorted by length, grouped (class, function, const).
- **No Yoda style**: `$var === true`, not `true === $var`.
- **Native function invocation**: `\count()`, `\array_map()`, `\array_key_exists()` (backslash-prefixed).
- **PHPDoc tag order**: `@inheritDoc` > `@test` > `@dataProvider` > `@template` > `@param` > `@return` > `@uses` > `@throws`.
- **PHPUnit tests**: `#[Test]` attribute, camelCase method names, `/** @internal */` on test class, `final` test class.

## Available Traits

| Trait | What it adds | When to use |
|-------|-------------|-------------|
| `FilterableAggregation` | `query(QueryInterface): static` + `addFilterToAggregation(array, name): array` | Aggregation supports filter context |
| `GlobalizableAggregation` | `asGlobal(): static` + `addGlobalToAggregation(array, name): array` | Aggregation can ignore top-level query |
| `SizeableAggregation` | `size(int): static` + `addSizeToAggregation(array): array` | Bucket aggregation supports size limits |

### Trait chaining order in toArray()

The order of trait helper calls in `toArray()` matters. Follow this exact order:

1. Build the base aggregation array (e.g. `['terms' => ['field' => ...]]`)
2. `addSizeToAggregation($value)` — adds `size` key to inner aggregation (before wrapping)
3. Wrap with aggregation type key if needed (e.g. `['{agg_name}' => $value]`)
4. `addFilterToAggregation($value, $this->getName())` — wraps in `filter` + `aggs`
5. `addGlobalToAggregation($value, $this->getName())` — wraps in `global` + `aggs`

For metrics aggregations (no size), the type key is in the base array:
```php
$value = ['{agg_name}' => ['field' => (string) $this->field]];
$value = $this->addFilterToAggregation($value, $this->getName());
$value = $this->addGlobalToAggregation($value, $this->getName());
```

For bucket aggregations (with size), size goes before wrapping:
```php
$value = ['field' => (string) $this->field];
$value = $this->addSizeToAggregation($value);
$value = ['{agg_name}' => $value];
$value = $this->addFilterToAggregation($value, $this->getName());
$value = $this->addGlobalToAggregation($value, $this->getName());
```

## Available Base Classes

| Class | Purpose | When to use |
|-------|---------|-------------|
| `AggregationInterface` | Contract for all aggregations | Always implement this |
| `CompositeAggregation` | Reusable aggregation combinations | User-defined domain composites |

## Reference Files

- `src/Aggregation/AggregationInterface.php` — interface contract (`getName()` + `toArray()`)
- `src/Aggregation/FilterableAggregation.php` — filter trait
- `src/Aggregation/GlobalizableAggregation.php` — global trait
- `src/Aggregation/SizeableAggregation.php` — size trait
- `src/Aggregation/StatsAggregation.php` — simplest metrics aggregation (reference)
- `src/Aggregation/TermsAggregation.php` — bucket aggregation with size (reference)
- `src/Aggregation/HistogramAggregation.php` — aggregation with constructor validation (reference)
- `src/Aggregation/NestedAggregation.php` — aggregation with sub-aggregations (reference)
- `src/Aggregation/ContainerAggregation.php` — container with global/filter validation (reference)
- `src/Aggregation/CompositeAggregation.php` — abstract for reusable composites
- `src/Exception/Aggregation/AggregationException.php` — abstract exception base
- `tests/Unit/Aggregation/StatsAggregationTest.php` — simplest test (reference)
- `tests/Unit/Aggregation/TermsAggregationTest.php` — test with all 3 traits (reference)
- `tests/Unit/Aggregation/Trait/FilterableAggregationTest.php` — trait test (reference)
- `tests/Unit/Aggregation/Trait/GlobalizableAggregationTest.php` — trait test (reference)
- `tests/Unit/Aggregation/Trait/SizeableAggregationTest.php` — trait test (reference)
- `tests/Fixture/BoolQueryFixture.php` — test fixture for filter queries
- `tests/TestCase.php` — base test class

## Verification

After creating all files, run:

```bash
composer code:analyse    # PHPStan — must pass with 0 errors
composer code:fix        # Rector + php-cs-fixer — apply auto-fixes
vendor/bin/phpunit --testsuite unit --filter {AggregationName}AggregationTest  # Tests must pass
```
