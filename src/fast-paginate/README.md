# Fast Paginate

> Forked from [hammerstonedev/fast-paginate](https://github.com/hammerstonedev/fast-paginate)

## About

Fast Paginate provides faster `limit`/`offset` pagination macros for Hyperf model builders and
relations. It is most useful for queries with large offsets, but performance depends on the data and
indexes, so benchmark it against the standard paginator for your application.

The component uses an approach similar to a deferred join. It first paginates a query that selects
the model's primary key and any selected aliases required for ordering. It then fetches the complete
rows for the keys on that page with a second query. Conceptually, the two data queries look like:

```sql
select contacts.id from contacts limit 15 offset 150000;
select * from contacts where contacts.id in (...);
```

The component executes separate queries rather than putting the limited query inside a `where in`
subquery. `fastPaginate()` also runs the normal count query needed for a length-aware paginator;
`simpleFastPaginate()` does not run that count query.

## Installation

```shell
composer require friendsofhyperf/fast-paginate
```

The component depends on Hyperf 3.2 packages. No configuration is required: Hyperf discovers the
component's `ConfigProvider`, which registers the pagination macros when the application boots.

## Usage

### Model builders and relations

Model builders and relations provide both macros:

```php
User::query()->fastPaginate();
User::query()->simpleFastPaginate();

User::first()->posts()->fastPaginate();
User::first()->posts()->simpleFastPaginate();
```

Their signatures match the corresponding Hyperf model-builder pagination methods:

```php
fastPaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null);
simpleFastPaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null);
```

- `$perPage`: items per page; `null` uses the model's configured per-page value.
- `$columns`: columns fetched for the complete rows.
- `$pageName`: query-string parameter used to resolve the current page.
- `$page`: explicit page number; `null` resolves it from the current request.

`fastPaginate()` returns a length-aware paginator with a total count. `simpleFastPaginate()` returns
a simple paginator that only determines whether more pages exist. `BelongsToMany` relations keep
their hydrated pivot data, and `HasManyThrough` relations are also supported.

### Scout builders

When `hyperf/scout` is installed, the component also registers `fastPaginate()` on
`Hyperf\Scout\Builder`:

```php
User::search('Hyperf')->fastPaginate();
```

Its signature is `fastPaginate($perPage = null, $pageName = 'page', $page = null)`. This Scout macro
delegates directly to Scout's normal `paginate()` method; it does not use the two-query database
optimization. `hyperf/scout` is not required by this component.

## Automatic fallbacks

When the optimized query shape is incompatible, the component automatically calls the corresponding
standard `paginate()` or `simplePaginate()` method. This happens when:

- the query contains `having`, `group by`, or `union` clauses;
- `$perPage` is `-1`;
- a selected expression required for ordering contains a `?` binding placeholder.

These fallbacks preserve pagination behavior, but they do not provide the fast-pagination
optimization.
