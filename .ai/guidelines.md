# AI Contribution Guidelines for php-elasticsearch-builder

## Purpose
- These rules guide AI-assisted code changes to keep the package consistent and maintainable.

## Scope
- Applies to all production code under `src/` and to PHPDoc/docblocks in Aggregation, Query, and Sort components.

## Hard Rules
- Visibility and finality in `src/`
    - Do not declare `final` on classes, methods, or constants anywhere under `src/`.
    - Do not use `private` visibility for properties or methods under `src/`.
    - Prefer `public` where appropriate or `protected` if limitation is necessary, but never `private`.

- Elasticsearch documentation references
    - Every Aggregation, Query, and Sort class defined under `src/` must include a class-level PHPDoc with a specific `@see` link to the official Elasticsearch documentation for that exact feature.
    - If a class represents a composite feature, include multiple `@see` tags (one per relevant Elasticsearch doc page).
    - Use stable Elastic documentation URLs (version-agnostic when available). Example formats:
      - Queries: `@see https://www.elastic.co/docs/reference/query-languages/query-dsl/<query-name>`
      - Aggregations: `@see https://www.elastic.co/docs/reference/elasticsearch/aggregation/<aggregation-name>`
      - Sorting: `@see https://www.elastic.co/docs/reference/elasticsearch/search/sort` (or a more specific subsection if applicable)

- PHPDoc on methods and properties
    - Every method and property under `src/` MUST have a PHPDoc/docblock.
    - Method PHPDoc must include the common tags applicable to the signature and behavior: `@param` for all parameters, `@return`, `@throws` for any thrown exceptions, and other standard tags as needed.
    - Property PHPDoc must include `@var` with the fully qualified class name (FQCN) or precise type. Do not rely on imported names inside PHPDoc; always use FQCNs.
    - In all PHPDoc tags, use FQCNs for class/interface/trait names (e.g., `\Namespace\ClassName`).

## Examples
- Class-level docblock for a Query:
  ```php
  /**
   * @see https://www.elastic.co/docs/reference/query-languages/query-dsl/query-dsl-bool-query
   */
  class BoolQuery implements QueryInterface { /* ... */ }
  ```

- Class-level docblock for an Aggregation:
  ```php
  /**
   * @see https://www.elastic.co/docs/reference/elasticsearch/aggregation/terms-aggregation
   */
  class TermsAggregation implements AggregationInterface { /* ... */ }
  ```

## Style & Conventions
- Keep strict types (`declare(strict_types=1);`).
- Match existing code style, naming, and formatting.
- Preserve existing public API and behavior; prefer additive, backward-compatible changes.

## Testing
- Each Aggregation, Query, and Sort class MUST be covered by dedicated unit tests under `tests/Unit`.
- Write as many tests as necessary per method to cover all logical cases and branches (including edge cases and error conditions).
- When adding or modifying behavior, update or create unit tests in `tests/Unit` accordingly.

## Review Checklist (AI/self-check before proposing changes)
- [ ] No `final` or `private` anywhere under `src/`.
- [ ] Every Aggregation/Query/Sort class has an accurate `@see` to Elastic docs.
- [ ] URLs point to the correct feature page and are not generic home pages.
- [ ] Code style and immutability patterns follow existing files.
- [ ] Every method and property under `src/` has PHPDoc with appropriate tags; all class names in PHPDoc use FQCNs.
- [ ] Each Aggregation/Query/Sort has unit tests in `tests/Unit` covering all logical branches and edge cases.
