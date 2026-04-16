# PROJECT KNOWLEDGE BASE

**Generated:** 2026-04-16
**Commit:** ddacee2
**Branch:** main

## OVERVIEW

Fluent, immutable, type-safe Elasticsearch query builder for PHP 8.4+. Zero production dependencies. Builds `array` payloads for the official `elasticsearch/elasticsearch` PHP client.

## STRUCTURE

```
./
├── src/
│   ├── QueryBuilder.php          # Main entry point - fluent builder
│   ├── Query/                    # Query classes (TermQuery, BoolQuery, MatchQuery, ...)
│   ├── Aggregation/              # Aggregation classes (TermsAggregation, StatsAggregation, ...)
│   ├── Sort/                     # Sort classes (FieldSort, ScoreSort) + SortDirectionEnum
│   └── Exception/                # Domain-specific exceptions (Aggregation/, Builder/, Query/)
├── tests/
│   ├── TestCase.php              # Base test class
│   ├── IntegrationTestCase.php   # Base for integration tests (ES client setup)
│   ├── Fixture/                  # Reusable test fixtures
│   ├── Unit/                     # Mirrors src/ structure
│   └── Integration/              # Requires running Elasticsearch
├── .agents/skills/               # Agent Skills (agentskills.io spec)
│   ├── create-query/SKILL.md     # Skill: create a new Query class
│   ├── create-aggregation/SKILL.md # Skill: create a new Aggregation class
│   └── create-sort/SKILL.md      # Skill: create a new Sort class
├── .ai/guidelines.md             # AI contribution rules (MUST READ before any changes)
├── scripts/import_dataset.php    # Imports test CSV into Elasticsearch
└── .data/                        # Local Elasticsearch data (Docker volume)
```

## WHERE TO LOOK

| Task | Location | Notes |
|------|----------|-------|
| Add new query type | `src/Query/` | Implement `QueryInterface`, use traits (`BoostableQuery`, `AnalyzerAwareQuery`) |
| Add new aggregation | `src/Aggregation/` | Implement `AggregationInterface`, use traits (`FilterableAggregation`, `GlobalizableAggregation`, `SizeableAggregation`) |
| Add new sort type | `src/Sort/` | Implement `SortInterface` |
| Add new exception | `src/Exception/{domain}/` | Extend domain base (`QueryException`, `AggregationException`, `BuilderException`) |
| Understand builder API | `src/QueryBuilder.php` | `query()`, `aggregation()`, `sort()`, `size()`, `from()`, `build()` |
| Understand immutability | Any concrete class | All mutation methods clone `$this` before modifying |
| Create composite query | Extend `CompositeQuery` | Override `query(): QueryInterface` |
| Create composite aggregation | Extend `CompositeAggregation` | Override `aggregation(): AggregationInterface` |
| Unit tests | `tests/Unit/` | Mirrors `src/` directory structure |
| Integration tests | `tests/Integration/` | Needs `ELASTICSEARCH_HOST` env var |
| Trait tests | `tests/Unit/Aggregation/Trait/` | Shared trait behavior tests |

## TYPE HIERARCHY

```
QueryInterface ─┬─ TermQuery (uses BoostableQuery)
                ├─ MatchQuery (uses BoostableQuery, AnalyzerAwareQuery)
                ├─ MatchPhraseQuery (uses BoostableQuery, AnalyzerAwareQuery)
                ├─ BoolQuery (uses BoostableQuery)
                ├─ NestedQuery
                ├─ RangeQuery [abstract] (uses BoostableQuery)
                │   ├─ NumericRangeQuery
                │   └─ DatetimeRangeQuery
                └─ CompositeQuery [abstract] ─ user-defined composites

AggregationInterface ─┬─ TermsAggregation (uses FilterableAggregation, GlobalizableAggregation, SizeableAggregation)
                      ├─ StatsAggregation (uses FilterableAggregation, GlobalizableAggregation)
                      ├─ NestedAggregation (uses FilterableAggregation, GlobalizableAggregation)
                      ├─ ContainerAggregation (uses FilterableAggregation, GlobalizableAggregation)
                      ├─ MultiTermsAggregation (uses FilterableAggregation, GlobalizableAggregation, SizeableAggregation)
                      ├─ HistogramAggregation (uses FilterableAggregation, GlobalizableAggregation)
                      └─ CompositeAggregation [abstract] ─ user-defined composites

SortInterface ─┬─ FieldSort
               └─ ScoreSort

Exception hierarchy:
  QueryException [abstract] ─ EmptyBoolQueryException, EmptyNestedQueryException, EmptyRangeQueryException, InvalidOperatorQueryException, InvalidRelationQueryException
  AggregationException [abstract] ─ DuplicatedContainerAggregationException, DuplicatedNestedAggregationException, InvalidAggregationSizeException, InvalidContainerAggregationException, InvalidIntervalException, NotEnoughFieldsAggregationException
  BuilderException [abstract] ─ DuplicatedBuilderAggregationException, InvalidFromException, InvalidSizeException
```

## AI CONTRIBUTION RULES (from .ai/guidelines.md)

### Hard Rules

- **No `final`** on classes, methods, or constants anywhere under `src/`.
- **No `private`** visibility for properties or methods under `src/`. Use `public` or `protected`.
- **`@see` Elastic docs** — every Query, Aggregation, and Sort class MUST have a class-level PHPDoc `@see` linking to the exact Elasticsearch documentation page.
  - Queries: `@see https://www.elastic.co/docs/reference/query-languages/query-dsl/<query-name>`
  - Aggregations: `@see https://www.elastic.co/docs/reference/elasticsearch/aggregation/<aggregation-name>`
  - Sorting: `@see https://www.elastic.co/docs/reference/elasticsearch/search/sort`
- **PHPDoc on everything** — every method and property under `src/` MUST have a docblock.
  - Methods: `@param`, `@return`, `@throws` (unless `@inheritDoc` is used and parent defines them).
  - Properties: `@var` with FQCN (e.g., `\Bonu\ElasticsearchBuilder\Query\QueryInterface`).
  - All class/interface/trait names in PHPDoc use FQCNs.

### Review Checklist

- [ ] No `final` or `private` anywhere under `src/`.
- [ ] Every Aggregation/Query/Sort class has `@see` to correct Elastic docs page (not generic).
- [ ] Every method and property has PHPDoc with appropriate tags; `@inheritDoc` accepted for methods only.
- [ ] FQCNs used in all PHPDoc type references.
- [ ] Unit tests in `tests/Unit/` covering all logical branches and edge cases.
- [ ] Code style matches existing patterns; `declare(strict_types=1)` present.
- [ ] Immutability preserved (clone before mutation).

## CONVENTIONS

- **Immutability**: All queries, aggregations, sorts are immutable. Every mutation method clones `$this`.
- **Strict types**: `declare(strict_types=1)` in every PHP file.
- **No production deps**: Only `php ^8.4` required. `elasticsearch/elasticsearch` is dev-only.
- **Class element order**: use_trait → constants → properties → construct → public methods → protected methods (enforced by php-cs-fixer `ordered_class_elements`).
- **Import order**: sorted by length, grouped (class → function → const).
- **No Yoda style**: `$var === true`, not `true === $var`.
- **PHPDoc tag order**: `@inheritDoc` → `@test` → `@dataProvider` → `@template` → `@param` → `@return` → `@uses` → `@throws`.
- **Native function invocation**: `\count()`, `\array_map()` (backslash-prefixed, enforced).
- **PHPUnit**: `#[Test]` attribute (not `@test` annotation), camelCase method names.

## ANTI-PATTERNS (THIS PROJECT)

- `final` keyword anywhere under `src/`
- `private` visibility under `src/`
- Missing `@see` on Query/Aggregation/Sort classes
- Missing PHPDoc on any method or property
- Short/imported names in PHPDoc types (use FQCNs)
- Mutable objects (forgetting to clone in mutation methods)
- `@author` or `@package` tags (removed by php-cs-fixer)

## COMMANDS

```bash
# Dev environment (Docker)
make start                           # Start Docker containers
make ws                              # Shell into workspace container
make phpunit                         # Run PHPUnit via Docker
make init-dataset                    # Import test CSV into Elasticsearch

# Code quality (inside container or local)
composer code:analyse                # PHPStan at max level on src/
composer code:fix                    # Rector + PHP-CS-Fixer auto-fix

# Direct tool invocation
vendor/bin/phpunit --testsuite unit           # Unit tests only
vendor/bin/phpunit --testsuite integration    # Integration tests (needs ES)
vendor/bin/phpstan analyse --memory-limit=-1  # Static analysis
vendor/bin/php-cs-fixer fix --dry-run         # Check code style
vendor/bin/rector process --dry-run           # Check Rector rules
```

## CI/CD

4 GitHub Actions workflows on PRs:
- **PHPUnit**: unit + integration suites, PHP 8.4 + 8.5 matrix, Coveralls coverage
- **PHPStan**: static analysis at max level
- **Code Style**: php-cs-fixer + rector (dry-run)
- **Release**: automated releases on main

## AGENT SKILLS

Skills in `.agents/skills/` follow the [agentskills.io](https://agentskills.io/specification.md) specification. Each skill is a directory containing a `SKILL.md` with YAML frontmatter (name, description) and step-by-step Markdown instructions.

| Skill | Directory | When to use |
|-------|-----------|-------------|
| `create-query` | `.agents/skills/create-query/` | Adding a new Elasticsearch Query class (`src/Query/`) — covers class, exception, tests, AGENTS.md update |
| `create-aggregation` | `.agents/skills/create-aggregation/` | Adding a new Elasticsearch Aggregation class (`src/Aggregation/`) — covers class, traits, exception, tests, trait tests, AGENTS.md update |
| `create-sort` | `.agents/skills/create-sort/` | Adding a new Elasticsearch Sort class (`src/Sort/`) — covers class, tests, AGENTS.md update |

Each skill contains:
- Concrete code templates matching this project's exact conventions (immutability, PHPDoc with FQCNs, no `final`/`private`, `@see` Elastic docs)
- Multiple templates per skill (simple, with validation, with traits, with sub-components)
- Unit test templates with `#[Test]`, `#[Depends]`, `#[DependsExternal]` patterns
- Hard rules checklist and verification commands

## NOTES

- PHPStan baseline ignores 2 false-positive "always false" comparisons in `QueryBuilder.php` (defensive `< 0` and `< 1` checks on validated ints).
- Integration tests require `ELASTICSEARCH_HOST` env var (default Docker: `localhost:9200`).
- `SortDirectionEnum` is a PHP 8.1+ backed enum (`ASC`/`DESC`).
- Rector skips `RemoveUselessParamTagRector`, `RemoveUselessReturnTagRector`, `RemoveUselessVarTagRector` — because this project mandates PHPDoc on everything per `.ai/guidelines.md`.
