# src/Query

Query classes implementing `QueryInterface`. All immutable; mutation methods clone `$this`.

## FILES

| File | Role | Traits |
|------|------|--------|
| `QueryInterface.php` | Contract: `toArray(): array` | — |
| `BoostableQuery.php` | Trait: `boost(float): static` | — |
| `AnalyzerAwareQuery.php` | Trait: `analyzer(string): static` | — |
| `CompositeQuery.php` | Abstract: user-defined reusable queries | — |
| `RangeQuery.php` | Abstract base for range queries | `BoostableQuery` |
| `TermQuery.php` | Exact value match | `BoostableQuery` |
| `MatchQuery.php` | Full-text match (OR/AND operators) | `BoostableQuery`, `AnalyzerAwareQuery` |
| `MatchPhraseQuery.php` | Phrase match with optional slop | `BoostableQuery`, `AnalyzerAwareQuery` |
| `BoolQuery.php` | must/should/mustNot/filter composition | `BoostableQuery` |
| `NestedQuery.php` | Query nested document paths | — |
| `NumericRangeQuery.php` | Numeric gt/gte/lt/lte | extends `RangeQuery` |
| `DatetimeRangeQuery.php` | Date gt/gte/lt/lte + format/timeZone | extends `RangeQuery` |

## ADDING A NEW QUERY

1. Create class implementing `QueryInterface`.
2. Use `BoostableQuery` trait if boost is supported.
3. Use `AnalyzerAwareQuery` trait if analyzer is supported.
4. Add class-level `@see` to exact Elastic docs page: `@see https://www.elastic.co/docs/reference/query-languages/query-dsl/<query-name>`.
5. PHPDoc every method and property (FQCNs in types).
6. No `final`, no `private`.
7. Ensure immutability: clone in every mutation method.
8. Add unit tests in `tests/Unit/Query/` covering all branches.

## COMPOSITE QUERIES

Extend `CompositeQuery`, override `query(): QueryInterface`. The abstract class delegates `toArray()` to the inner query. Use for reusable domain-specific query combinations (e.g., `PubliclyVisibleProductsQuery`).
