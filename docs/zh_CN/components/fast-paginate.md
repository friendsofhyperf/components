# Fast Paginate for Hyperf

> Fork from https://github.com/hammerstonedev/fast-paginate

## 关于

这是一个用于 Hyperf 的快速 `limit`/`offset` 分页宏。它可以替代标准的 `paginate` 方法。

这个包使用了一种类似于“延迟连接”的 SQL 方法来实现这种加速。延迟连接是一种在应用 `offset` 和 `limit` 之后才访问请求列的技术。

在我们的例子中，我们实际上并没有进行连接，而是使用了带有子查询的 `where in`。使用这种技术，我们创建了一个可以通过特定索引进行优化的子查询以达到最大速度，然后使用这些结果来获取完整的行。

SQL 语句如下所示：

```sql
select * from contacts              -- The full data that you want to show your users.
    where contacts.id in (          -- The "deferred join" or subquery, in our case.
        select id from contacts     -- The pagination, accessing as little data as possible - ID only.
        limit 15 offset 150000
    )
```

> 运行上述查询时，您可能会遇到错误！例如 `This version of MySQL doesn't yet support 'LIMIT & IN/ALL/ANY/SOME subquery.` 
> 在这个包中，我们将它们作为[两个独立的查询](https://github.com/hammerstonedev/fast-paginate/blob/154da286f8160a9e75e64e8025b0da682aa2ba23/src/BuilderMixin.php#L62-L79)来运行以解决这个问题！

根据您的数据集，性能提升可能会有所不同，但这种方法允许数据库检查尽可能少的数据以满足用户的需求。

虽然这种方法不太可能比传统的 `offset` / `limit` 性能更差，但也有可能，所以请务必在您的数据上进行测试！

> 如果您想阅读关于这个包理论的 3,000 字文章，可以访问 [aaronfrancis.com/2022/efficient-pagination-using-deferred-joins](https://aaronfrancis.com/2022/efficient-pagination-using-deferred-joins)。

## 安装

```shell
composer require friendsofhyperf/fast-paginate
```

无需执行其他操作，服务提供者将由 Hyperf 自动加载。

## 使用

在任何您会使用 `Model::query()->paginate()` 的地方，您都可以使用 `Model::query()->fastPaginate()`！就是这么简单！方法签名是相同的。

关系也同样支持：

```php
User::first()->posts()->fastPaginate();
```
