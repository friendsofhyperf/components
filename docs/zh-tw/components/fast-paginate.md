# Fast Paginate

> Forked from [hammerstonedev/fast-paginate](https://github.com/hammerstonedev/fast-paginate)

## 關於

Fast Paginate 為 Hyperf 模型查詢建構器和關聯提供更快的 `limit`/`offset` 分頁巨集。它最適合偏移量較大的
查詢，但實際效能取決於資料和索引，請在應用程式中與標準分頁器進行基準測試。

該元件使用類似延遲連線的方式。它首先對僅選擇模型主鍵以及排序所需已選別名的查詢進行分頁，然後透過第二次
查詢取得目前頁面主鍵對應的完整資料。兩次資料查詢的概念形式如下：

```sql
select contacts.id from contacts limit 15 offset 150000;
select * from contacts where contacts.id in (...);
```

元件會執行獨立查詢，而不是把帶限制的查詢放入 `where in` 子查詢中。`fastPaginate()` 還會執行長度感知
分頁器所需的標準計數查詢；`simpleFastPaginate()` 不會執行該計數查詢。

## 安裝

```shell
composer require friendsofhyperf/fast-paginate
```

該元件依賴 Hyperf 3.2 系列套件。無需設定：Hyperf 會發現元件的 `ConfigProvider`，並在應用程式啟動時註冊
分頁巨集。

## 使用

### 模型查詢建構器和關聯

模型查詢建構器和關聯均提供以下兩個巨集：

```php
User::query()->fastPaginate();
User::query()->simpleFastPaginate();

User::first()->posts()->fastPaginate();
User::first()->posts()->simpleFastPaginate();
```

它們的簽章與對應的 Hyperf 模型查詢建構器分頁方法一致：

```php
fastPaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null);
simpleFastPaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null);
```

- `$perPage`：每頁項目數；`null` 使用模型設定的每頁項目數。
- `$columns`：取得完整資料時查詢的欄位。
- `$pageName`：用於解析目前頁面的查詢字串參數名稱。
- `$page`：明確指定的頁碼；`null` 時從目前請求解析。

`fastPaginate()` 回傳包含總數的長度感知分頁器。`simpleFastPaginate()` 回傳僅判斷是否還有下一頁的簡單
分頁器。`BelongsToMany` 關聯會保留已填充的中介表資料，同時也支援 `HasManyThrough` 關聯。

### Scout 查詢建構器

安裝 `hyperf/scout` 後，元件還會在 `Hyperf\Scout\Builder` 上註冊 `fastPaginate()`：

```php
User::search('Hyperf')->fastPaginate();
```

其簽章為 `fastPaginate($perPage = null, $pageName = 'page', $page = null)`。該 Scout 巨集會直接呼叫
Scout 的標準 `paginate()` 方法，不使用資料庫兩次查詢最佳化。本元件不強制依賴 `hyperf/scout`。

## 自動回退

當查詢結構與最佳化方式不相容時，元件會自動呼叫對應的標準 `paginate()` 或 `simplePaginate()` 方法。
以下情況會觸發回退：

- 查詢包含 `having`、`group by` 或 `union` 子句；
- `$perPage` 為 `-1`；
- 排序所需的已選運算式包含 `?` 綁定預留位置。

這些回退會保留分頁行為，但不會獲得快速分頁最佳化。
