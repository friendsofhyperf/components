# Fast Paginate

> Forked from [hammerstonedev/fast-paginate](https://github.com/hammerstonedev/fast-paginate)

## 关于

Fast Paginate 为 Hyperf 模型查询构造器和关系提供更快的 `limit`/`offset` 分页宏。它最适合偏移量较大的
查询，但实际性能取决于数据和索引，请在应用中与标准分页器进行基准测试。

该组件使用类似延迟连接的方式。它首先对仅选择模型主键以及排序所需选中别名的查询进行分页，然后通过第二次
查询获取当前页主键对应的完整数据。两次数据查询的概念形式如下：

```sql
select contacts.id from contacts limit 15 offset 150000;
select * from contacts where contacts.id in (...);
```

组件会执行独立查询，而不是把带限制的查询放入 `where in` 子查询中。`fastPaginate()` 还会执行长度感知
分页器所需的标准计数查询；`simpleFastPaginate()` 不会执行该计数查询。

## 安装

```shell
composer require friendsofhyperf/fast-paginate
```

该组件依赖 Hyperf 3.2 系列软件包。无需配置：Hyperf 会发现组件的 `ConfigProvider`，并在应用启动时注册
分页宏。

## 使用

### 模型查询构造器和关系

模型查询构造器和关系均提供以下两个宏：

```php
User::query()->fastPaginate();
User::query()->simpleFastPaginate();

User::first()->posts()->fastPaginate();
User::first()->posts()->simpleFastPaginate();
```

它们的签名与对应的 Hyperf 模型查询构造器分页方法一致：

```php
fastPaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null);
simpleFastPaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null);
```

- `$perPage`：每页条目数；`null` 使用模型配置的每页条目数。
- `$columns`：获取完整数据时查询的列。
- `$pageName`：用于解析当前页的查询字符串参数名。
- `$page`：明确指定的页码；`null` 时从当前请求解析。

`fastPaginate()` 返回包含总数的长度感知分页器。`simpleFastPaginate()` 返回仅判断是否还有下一页的简单
分页器。`BelongsToMany` 关系会保留已填充的中间表数据，同时也支持 `HasManyThrough` 关系。

### Scout 查询构造器

安装 `hyperf/scout` 后，组件还会在 `Hyperf\Scout\Builder` 上注册 `fastPaginate()`：

```php
User::search('Hyperf')->fastPaginate();
```

其签名为 `fastPaginate($perPage = null, $pageName = 'page', $page = null)`。该 Scout 宏会直接调用
Scout 的标准 `paginate()` 方法，不使用数据库两次查询优化。本组件不强制依赖 `hyperf/scout`。

## 自动回退

当查询结构与优化方式不兼容时，组件会自动调用对应的标准 `paginate()` 或 `simplePaginate()` 方法。
以下情况会触发回退：

- 查询包含 `having`、`group by` 或 `union` 子句；
- `$perPage` 为 `-1`；
- 排序所需的选中表达式包含 `?` 绑定占位符。

这些回退会保留分页行为，但不会获得快速分页优化。
