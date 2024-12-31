# Fast Paginate

> Forked from [hammerstonedev/fast-paginate](https://github.com/hammerstonedev/fast-paginate)

## 關於

這是一個用於 Hyperf 的快速 `limit`/`offset` 分頁宏。它可以替代標準的 `paginate` 方法。

這個包使用了一種類似於“延遲連接”的 SQL 方法來實現這種加速。延遲連接是一種在應用 `offset` 和 `limit` 之後才訪問請求列的技術。

在我們的例子中，我們實際上並沒有進行連接，而是使用了帶有子查詢的 `where in`。使用這種技術，我們創建了一個可以通過特定索引進行優化的子查詢以達到最大速度，然後使用這些結果來獲取完整的行。

SQL 語句如下所示：

```sql
select * from contacts              -- The full data that you want to show your users.
    where contacts.id in (          -- The "deferred join" or subquery, in our case.
        select id from contacts     -- The pagination, accessing as little data as possible - ID only.
        limit 15 offset 150000
    )
```

> 運行上述查詢時，您可能會遇到錯誤！例如 `This version of MySQL doesn't yet support 'LIMIT & IN/ALL/ANY/SOME subquery.`
> 在這個包中，我們將它們作為[兩個獨立的查詢](https://github.com/hammerstonedev/fast-paginate/blob/154da286f8160a9e75e64e8025b0da682aa2ba23/src/BuilderMixin.php#L62-L79)來運行以解決這個問題！

根據您的數據集，性能提升可能會有所不同，但這種方法允許數據庫檢查儘可能少的數據以滿足用户的需求。

雖然這種方法不太可能比傳統的 `offset` / `limit` 性能更差，但也有可能，所以請務必在您的數據上進行測試！

> 如果您想閲讀關於這個包理論的 3,000 字文章，可以訪問 [aaronfrancis.com/2022/efficient-pagination-using-deferred-joins](https://aaronfrancis.com/2022/efficient-pagination-using-deferred-joins)。

## 安裝

```shell
composer require friendsofhyperf/fast-paginate
```

無需執行其他操作，服務提供者將由 Hyperf 自動加載。

## 使用

在任何您會使用 `Model::query()->paginate()` 的地方，您都可以使用 `Model::query()->fastPaginate()`！就是這麼簡單！方法簽名是相同的。

關係也同樣支持：

```php
User::first()->posts()->fastPaginate();
```
