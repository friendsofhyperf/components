# Fast Paginate

> Forked from [hammerstonedev/fast-paginate](https://github.com/hammerstonedev/fast-paginate)

## About

This is a fast `limit`/`offset` pagination macro for Hyperf. It can be used as a drop-in replacement for the standard `paginate` method.

This package uses a SQL technique similar to a "deferred join" to achieve this speedup. A deferred join is a technique where you only access the requested columns after applying the `offset` and `limit`.

In our case, we're not actually doing a join, but rather using a `where in` with a subquery. Using this technique, we create a subquery that can be optimized through a specific index for maximum speed, and then use those results to get the full rows.

The SQL looks like this:

```sql
select * from contacts              -- The full data that you want to show your users.
    where contacts.id in (          -- The "deferred join" or subquery, in our case.
        select id from contacts     -- The pagination, accessing as little data as possible - ID only.
        limit 15 offset 150000
    )
```

> When running the query above, you might encounter errors! For example `This version of MySQL doesn't yet support 'LIMIT & IN/ALL/ANY/SOME subquery.` 
> In this package, we run them as [two separate queries](https://github.com/hammerstonedev/fast-paginate/blob/154da286f8160a9e75e64e8025b0da682aa2ba23/src/BuilderMixin.php#L62-L79) to solve this problem!

Performance improvements will vary depending on your dataset, but this approach allows the database to examine as little data as possible to satisfy the user's request.

While it's unlikely that this approach will perform worse than traditional `offset` / `limit`, it's possible, so be sure to test it on your data!

> If you want to read a 3,000-word article about the theory behind this package, visit [aaronfrancis.com/2022/efficient-pagination-using-deferred-joins](https://aaronfrancis.com/2022/efficient-pagination-using-deferred-joins).

## Installation

```shell
composer require friendsofhyperf/fast-paginate
```

No other steps are required, the service provider will be automatically loaded by Hyperf.

## Usage

Anywhere you would use `Model::query()->paginate()`, you can use `Model::query()->fastPaginate()`! It's that simple! The method signature is the same.

Relations are also supported:

```php
User::first()->posts()->fastPaginate();
```
