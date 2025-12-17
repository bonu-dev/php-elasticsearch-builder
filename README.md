# PHP Query Builder for Elasticsearch

[![PHPUnit](https://github.com/bonu-dev/php-elasticsearch-builder/actions/workflows/phpunit.yaml/badge.svg?branch=main)](https://github.com/bonu-dev/php-elasticsearch-builder/actions/workflows/phpunit.yaml)
[![Latest Stable Version](https://poser.pugx.org/bonu/php-elasticsearch-builder/v)](https://packagist.org/packages/bonu/php-elasticsearch-builder)
[![License](https://poser.pugx.org/bonu/php-elasticsearch-builder/license)](https://packagist.org/packages/bonu/php-elasticsearch-builder)
[![PHP Version](https://img.shields.io/packagist/php-v/bonu/php-elasticsearch-builder.svg)](https://packagist.org/packages/bonu/php-elasticsearch-builder)

---

**A clean, fluent, immutable, and type-safe query builder for Elasticsearch** - built from the ground up to work seamlessly with the [official Elasticsearch PHP client](https://github.com/elastic/elasticsearch-php).

No extra dependencies. No magic. Just expressive, readable, and maintainable Elasticsearch queries in PHP.

```php
use Elastic\Elasticsearch\ClientBuilder;
use Bonu\ElasticsearchBuilder\QueryBuilder;
use Bonu\ElasticsearchBuilder\Query\TermQuery;
use Bonu\ElasticsearchBuilder\Query\BoolQuery;
use Bonu\ElasticsearchBuilder\Query\MatchQuery;

$builder = new QueryBuilder('products')
    ->query(new TermQuery('ean', 'foo_bar_123')->boost(12))
    ->query(new BoolQuery()
        ->should(new MatchQuery('name', 'foo'))
        ->should(new MatchQuery('description', 'bar'))
        ->boost(5)
    )
    ->size(20);

$client = ClientBuilder::create()->build();
$products = $client->search($builder->build());
```

## Features

- Fully fluent & chainable API
- Zero dependencies beyond the official Elasticsearch PHP SDK
- Easy creation of reusable composite queries
- 100% type-hinted and IDE-friendly

## Requirements

- PHP ≥ 8.4

## Installation

```bash
composer require bonu/php-elasticsearch-builder
```

## Using query builder

The `Bonu\ElasticsearchBuilder\QueryBuilder` class provides a fluent interface for building Elasticsearch queries.
Constructor of this class accepts a single argument - name of the index to query, which may be used for searching in specific index.

Out of box it supports:
- Chaining queries using `query()` method
- Chaining aggregations using `aggregation()` method
- Configuring pagination of results using `from()` and `size()` methods

## Queries

> **Note** Queries are immutable.

This package comes with a set of ready-to-use queries which is documented below.

It is also possible to create reusable composite queries using abstract `Bonu\ElasticsearchBuilder\Query\CompositeQuery` class.

Example of composite query:

```php
use Bonu\ElasticsearchBuilder\Query\BoolQuery;
use Bonu\ElasticsearchBuilder\Query\TermQuery;use Bonu\ElasticsearchBuilder\Query\CompositeQuery;

class PubliclyVisibleProductsQuery extends CompositeQuery
{
    /**
     * @inheritDoc
     */
    public function query(): QueryInterface
    {
        return new BoolQuery()
            ->must(new TermQuery('is_active', true))
            ->mustNot(new TermQuery('is_out_of_stock', false));
    }
}

$builder = new QueryBuilder('products')
    ->query(new PubliclyVisibleProductsQuery());
```

### TermQuery

https://www.elastic.co/docs/reference/query-languages/query-dsl/query-dsl-term-query

```php
use Bonu\ElasticsearchBuilder\Query\TermQuery;

new TermQuery('field', 'value')->boost(10)
```

### MatchQuery

https://www.elastic.co/docs/reference/query-languages/query-dsl/query-dsl-match-query

```php
use Bonu\ElasticsearchBuilder\Query\MatchQuery;

// Default operator is OR
new MatchQuery('field', 'some text')
    ->boost(2)
    ->analyzer('standard')

// With AND operator
new MatchQuery('field', 'some text', MatchQuery::OPERATOR_AND)
```

### MatchPhraseQuery

https://www.elastic.co/docs/reference/query-languages/query-dsl/query-dsl-match-query-phrase

```php
use Bonu\ElasticsearchBuilder\Query\MatchPhraseQuery;

// Optional third argument is slop
new MatchPhraseQuery('field', 'exact phrase', 2)
    ->boost(1.5)
    ->analyzer('standard')
```

### BoolQuery

https://www.elastic.co/docs/reference/query-languages/query-dsl/query-dsl-bool-query

```php
use Bonu\ElasticsearchBuilder\Query\BoolQuery;
use Bonu\ElasticsearchBuilder\Query\TermQuery;
use Bonu\ElasticsearchBuilder\Query\MatchQuery;

new BoolQuery()
    ->must(new TermQuery('status', 'active'))
    ->filter(new TermQuery('stock', 1))
    ->should(new MatchQuery('title', 'awesome product'))
    ->mustNot(new TermQuery('blocked', true))
    ->boost(3)
```

### NestedQuery

https://www.elastic.co/docs/reference/query-languages/query-dsl/query-dsl-nested-query

```php
use Bonu\ElasticsearchBuilder\Query\NestedQuery;
use Bonu\ElasticsearchBuilder\Query\MatchQuery;

// Query nested field path, inner query must be provided via ->query()
new NestedQuery('variants')
    ->query(new MatchQuery('variants.name', 'red'))
```

### RangeQuery

https://www.elastic.co/docs/reference/query-languages/query-dsl/query-dsl-range-query

Range queries can be used for filtering by multiple data types. For this reason, each data type has its own query class to fully support type-hinting.

```php
use Bonu\ElasticsearchBuilder\Query\NumericRangeQuery;
use Bonu\ElasticsearchBuilder\Query\DatetimeRangeQuery;

new NumericRangeQuery('price', gte: 100)
    ->boost(10);

new DatetimeRangeQuery('created_at', lt: date('Y-m-d'), format: 'yyyy-MM-dd', timeZone: 'Europe/Prague')
    ->boost(20);
```

## Aggregations

> **Note** Aggregations are immutable.

Similar to queries, it is also possible to create reusable composite aggregations using abstract `Bonu\ElasticsearchBuilder\Aggregation\CompositeAggregation` class.

Example of composite aggregation:

```php
use Bonu\ElasticsearchBuilder\Aggregation\TermsAggregation;
use Bonu\ElasticsearchBuilder\Aggregation\NestedAggregation;
use Bonu\ElasticsearchBuilder\Aggregation\CompositeAggregation;

class CategoryBrandAggregation extends CompositeAggregation
{
    /**
     * @param string|\Stringable $name
     */
    public function __construct(
        private readonly string | Stringable $name,
    ) {}

    /**
     * @inheritDoc
     */
    public function aggregation(): AggregationInterface
    {
        return new NestedAggregation($this->name, 'products')
            ->aggregation(new TermsAggregation('by_brand', 'products.brand_id'));
    }
}
```

### TermsAggregation

https://www.elastic.co/docs/reference/aggregations/search-aggregations-bucket-terms-aggregation

```php
use Bonu\ElasticsearchBuilder\Aggregation\TermsAggregation;
use Bonu\ElasticsearchBuilder\Query\TermQuery;

// Top 10 brands, filtered to active products
new TermsAggregation('by_brand', 'brand.keyword')
    ->size(10)
    ->query(new TermQuery('status', 'active'));

// Make the aggregation global (ignores the top-level query)
new TermsAggregation('all_categories', 'category.keyword')
    ->asGlobal();
```

### StatsAggregation

https://www.elastic.co/docs/reference/aggregations/search-aggregations-metrics-stats-aggregation

```php
use Bonu\ElasticsearchBuilder\Aggregation\StatsAggregation;
use Bonu\ElasticsearchBuilder\Query\TermQuery;

// Basic stats for the price field, filtered by currency
new StatsAggregation('price_stats', 'price')
    ->query(new TermQuery('currency', 'USD'));

// Make the aggregation global (ignores the top-level query)
new StatsAggregation('global_price_stats', 'price')
    ->asGlobal();
```

## NestedAggregation

https://www.elastic.co/docs/reference/aggregations/search-aggregations-bucket-nested-aggregation

```php
use Bonu\ElasticsearchBuilder\Aggregation\NestedAggregation;

new NestedAggregation('categories', 'products')
    ->aggregation(new StatsAggregation('product_price', 'products.price'))
```

## Sorts

### FieldSort

```php

use Bonu\ElasticsearchBuilder\Sort\FieldSort;
use Bonu\ElasticsearchBuilder\Sort\SortDirectionEnum;

new FieldSort('my_field', SortDirectionEnum::ASC)
```

### ScoreSort

```php

use Bonu\ElasticsearchBuilder\Sort\ScoreSort;
use Bonu\ElasticsearchBuilder\Sort\SortDirectionEnum;

new ScoreSort(SortDirectionEnum::DESC)
```

## License

This package is licensed under the MIT License.
