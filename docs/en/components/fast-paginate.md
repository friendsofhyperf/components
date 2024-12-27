# Fast Paginate

> Forked from [hammerstonedev/fast-paginate](https://github.com/hammerstonedev/fast-paginate)

## About

This is a fast `limit`/`offset` pagination macro for Hyperf. It serves as a replacement for the standard `paginate` method.

This package utilizes a SQL technique similar to “deferred joins” to achieve speed improvements. Deferred joins are a technique where the requested columns are accessed only after applying `offset` and `limit`.

In our implementation, we are not actually performing a join, but instead using a `where in` clause with a subquery. With this approach, we create a subquery optimized by a specific index for maximum performance, and then use these results to retrieve the full rows.

The SQL query looks like this:

```sql
select * from contacts              -- The full data that you want to show your users.
    where contacts.id in (          -- The "deferred join" or subquery, in our case.
        select id from contacts     -- The pagination, accessing as little data as possible - ID only.
        limit 15 offset 150000
    )
```

> When running the query above, you might encounter errors! For example, `This version of MySQL doesn't yet support 'LIMIT & IN/ALL/ANY/SOME subquery.` 
> In this package, we run these as [two separate queries](https://github.com/hammerstonedev/fast-paginate/blob/154da286f8160a9e75e64e8025b0da682aa2ba23/src/BuilderMixin.php#L62-L79) to mitigate this problem!

Depending on your dataset, the performance improvement may vary, but this approach allows the database to inspect as little data as possible to fulfill the user's request.

While this method is unlikely to perform worse than traditional `offset`/`limit`, it is still a possibility, so be sure to test on your dataset!

> If you'd like to read a 3,000-word article on the theory behind this package, visit [aaronfrancis.com/2022/efficient-pagination-using-deferred-joins](https://aaronfrancis.com/2022/efficient-pagination-using-deferred-joins).

## Installation

```shell
composer require friendsofhyperf/fast-paginate
```

No further steps are required. The service provider will be automatically loaded by Hyperf.

## Usage

Wherever you would use `Model::query()->paginate()`, you can use `Model::query()->fastPaginate()` instead! It's that simple! The method signature is the same.

Relationships are also supported:

```php
User::first()->posts()->fastPaginate();
```