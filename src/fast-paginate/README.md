# Fast Paginate for Hyperf

> Fork from https://github.com/hammerstonedev/fast-paginate

## About

This is a fast `limit`/`offset` pagination macro for Hyperf. It can be used in place of the standard `paginate` methods.

This package uses a SQL method similar to a "deferred join" to achieve this speedup. A deferred join is a technique that defers access to requested columns until _after_ the `offset` and `limit` have been applied.

In our case we don't actually do a join, but rather a `where in` with a subquery. Using this technique we create a subquery that can be optimized with specific indexes for maximum speed and then use those results to fetch the full rows.

The SQL looks something like this:

```sql
select * from contacts              -- The full data that you want to show your users.
    where contacts.id in (          -- The "deferred join" or subquery, in our case.
        select id from contacts     -- The pagination, accessing as little data as possible - ID only.
        limit 15 offset 150000
    )
```

> You might get an error trying to run the query above! Something like `This version of MySQL doesn't yet support 'LIMIT & IN/ALL/ANY/SOME subquery.`
> In this package, we run them as [two _separate_ queries](https://github.com/hammerstonedev/fast-paginate/blob/154da286f8160a9e75e64e8025b0da682aa2ba23/src/BuilderMixin.php#L62-L79) to get around that!  

The benefits can vary based on your dataset, but this method allows the database to examine as little data as possible to satisfy the user's intent.

It's unlikely that this method will ever perform worse than traditional `offset` / `limit`, although it is possible, so be
sure to test on your data!

> If you want to read 3,000 words on the theory of this package, you can head over to [aaronfrancis.com/2022/efficient-pagination-using-deferred-joins](https://aaronfrancis.com/2022/efficient-pagination-using-deferred-joins).

## Installation

This package supports Hyperf 3.0+.

To install, require the package via composer:

```shell
composer require friendsofhyperf/fast-paginate
```

There is nothing further you need to do. The service provider will be loaded automatically by Hyperf.

## Usage

Anywhere you would use `Model::query()->paginate()`, you can use `Model::query()->fastPaginate()`! That's it! The method signature is the same.

Relationships are supported as well:

```php
User::first()->posts()->fastPaginate();
```

## A Favor

If this helps you, please [start me](https://github.com/friendsofhyperf/fast-paginate) with before and after times! I'd love to know :D

Some community results so far:
* [30 seconds --> 250ms](https://twitter.com/mdavis1982/status/1482429071288066054)
* [28 seconds --> 2 seconds](https://twitter.com/joecampo/status/1483550610028957701)
* [7.5x faster](https://twitter.com/max_eckel/status/1483764319372333057)
* [1.1 seconds --> 0.1 seconds](https://twitter.com/max_eckel/status/1483852300414337032)
* [20 seconds --> 2 seconds](https://twitter.com/1ralphmorris/status/1484242437618941957)
* [2 seconds --> .2 seconds](https://twitter.com/julioelpoeta/status/1549524738980077568)

## Donate

> If you like them, Buy me a cup of coffee.

| Alipay | WeChat | Buy Me A Coffee |
|  ----  |  ----  |  ----  |
| <img src="https://hdj.me/images/alipay-min.jpg" width="200" height="200" />  | <img src="https://hdj.me/images/wechat-pay-min.jpg" width="200" height="200" /> | <img src="https://hdj.me/images/bmc_qr.png" width="200" height="200" /> |

<a href="https://www.buymeacoffee.com/huangdijiag" target="_blank"><img src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" alt="Buy Me A Coffee" style="height: 60px !important;width: 217px !important;" ></a>

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
