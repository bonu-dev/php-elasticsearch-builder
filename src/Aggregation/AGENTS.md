# src/Aggregation

Aggregation classes implementing `AggregationInterface`. All immutable; mutation methods clone `$this`.

## FILES

| File | Role | Traits |
|------|------|--------|
| `AggregationInterface.php` | Contract: `getName(): string`, `toArray(): array` | — |
| `FilterableAggregation.php` | Trait: `query(QueryInterface): static` — adds filter context | — |
| `GlobalizableAggregation.php` | Trait: `asGlobal(): static` — ignores top-level query | — |
| `SizeableAggregation.php` | Trait: `size(int): static` — limits bucket count | — |
| `CompositeAggregation.php` | Abstract: user-defined reusable aggregations | — |
| `TermsAggregation.php` | Bucket by field values | `FilterableAggregation`, `GlobalizableAggregation`, `SizeableAggregation` |
| `StatsAggregation.php` | min/max/avg/sum/count metrics | `FilterableAggregation`, `GlobalizableAggregation` |
| `NestedAggregation.php` | Aggregate on nested document paths | `FilterableAggregation`, `GlobalizableAggregation` |
| `ContainerAggregation.php` | Groups sub-aggregations with filter or global | `FilterableAggregation`, `GlobalizableAggregation` |
| `MultiTermsAggregation.php` | Bucket by multiple fields | `FilterableAggregation`, `GlobalizableAggregation`, `SizeableAggregation` |
| `HistogramAggregation.php` | Fixed-interval numeric buckets | `FilterableAggregation`, `GlobalizableAggregation` |
| `SumAggregation.php` | Sum of numeric field values | `FilterableAggregation`, `GlobalizableAggregation` |
| `CardinalityAggregation.php` | Distinct value count (cardinality) | `FilterableAggregation`, `GlobalizableAggregation` |

## ADDING A NEW AGGREGATION

1. Create class implementing `AggregationInterface`.
2. Use `FilterableAggregation` trait if the aggregation supports filter context.
3. Use `GlobalizableAggregation` trait if the aggregation can be made global.
4. Use `SizeableAggregation` trait if the aggregation supports size limits.
5. Add class-level `@see` to exact Elastic docs page: `@see https://www.elastic.co/docs/reference/elasticsearch/aggregation/<aggregation-name>`.
6. PHPDoc every method and property (FQCNs in types).
7. No `final`, no `private`.
8. Ensure immutability: clone in every mutation method.
9. Add unit tests in `tests/Unit/Aggregation/` covering all branches.
10. If adding a new trait, add trait tests in `tests/Unit/Aggregation/Trait/`.

## CONTAINER AGGREGATION

`ContainerAggregation` requires either `asGlobal()` or at least one `query()` filter — never both. Throws `InvalidContainerAggregationException` on `toArray()` if neither or both are set.

## COMPOSITE AGGREGATIONS

Extend `CompositeAggregation`, override `aggregation(): AggregationInterface`. The abstract class delegates `getName()` and `toArray()` to the inner aggregation. Use for reusable domain-specific aggregation combinations.
