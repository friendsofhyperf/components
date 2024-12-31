# Fast Paginate

> Forked from [hammerstonedev/fast-paginate](https://github.com/hammerstonedev/fast-paginate)

## About

This is a fast `limit`/`offset` pagination macro for Hyperf. It can replace the standard `paginate` method.

This package uses an SQL approach similar to "deferred joins" to achieve this speedup. Deferred joins are a technique where the requested columns are accessed only after applying the `offset` and `limit`.

In our case, we don't actually perform a join but use a `where in` with a subquery. Using this technique, we create a subquery that can be optimized by a specific index for maximum speed, and then use these results to fetch the full rows.

The SQL statement looks like this:

```sql
select * from contacts              -- The full data that you want to show your users.
    where contacts.id in (          -- The "deferred join" or subquery, in our case.
        select id from contacts     -- The pagination, accessing as little data as possible - ID only.
        limit 15 offset 150000
    )
```

> When running the above query, you might encounter an error! For example, `This version of MySQL doesn't yet support 'LIMIT & IN/ALL/ANY/SOME subquery.`
> In this package, we run them as [two separate queries](https://github.com/hammerstonedev/fast-paginate/blob/154da286f8160a9e75e64e8025b0da682aa2ba23/src/BuilderMixin.php#L62-L79) to solve this issue!

Depending on your dataset, the performance improvement may vary, but this approach allows the database to examine as little data as possible to meet the user's needs.

While this method is unlikely to perform worse than traditional `offset` / `limit`, it is possible, so be sure to test it on your data!

> If you want to read a 3,000-word article on the theory behind this package, visit [aaronfrancis.com/2022/efficient-pagination-using-deferred-joins](https://aaronfrancis.com/2022/efficient-pagination-using-deferred-joins).

## Installation

```shell
composer require friendsofhyperf/fast-paginate
```

No additional steps are required; the service provider will be automatically loaded by Hyperf.

## Usage

Anywhere you would use `Model::query()->paginate()`, you can use `Model::query()->fastPaginate()`! It's that simple! The method signature is the same.

Relations are also supported:

```php
User::first()->posts()->fastPaginate();
```