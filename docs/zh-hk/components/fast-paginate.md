# Fast Paginate

> Forked from [hammerstonedev/fast-paginate](https://github.com/hammerstonedev/fast-paginate)

## 關於

Fast Paginate 為 Hyperf 模型查詢構造器和關係提供更快的 `limit`/`offset` 分頁宏。它最適合偏移量較大的
查詢，但實際性能取決於數據和索引，請在應用中與標準分頁器進行基準測試。

該組件使用類似延遲連接的方式。它首先對僅選擇模型主鍵以及排序所需選中別名的查詢進行分頁，然後通過第二次
查詢獲取當前頁主鍵對應的完整數據。兩次數據查詢的概念形式如下：

```sql
select contacts.id from contacts limit 15 offset 150000;
select * from contacts where contacts.id in (...);
```

組件會執行獨立查詢，而不是把帶限制的查詢放入 `where in` 子查詢中。`fastPaginate()` 還會執行長度感知
分頁器所需的標準計數查詢；`simpleFastPaginate()` 不會執行該計數查詢。

## 安裝

```shell
composer require friendsofhyperf/fast-paginate
```

該組件依賴 Hyperf 3.2 系列軟件包。無需配置：Hyperf 會發現組件的 `ConfigProvider`，並在應用啓動時註冊
分頁宏。

## 使用

### 模型查詢構造器和關係

模型查詢構造器和關係均提供以下兩個宏：

```php
User::query()->fastPaginate();
User::query()->simpleFastPaginate();

User::first()->posts()->fastPaginate();
User::first()->posts()->simpleFastPaginate();
```

它們的簽名與對應的 Hyperf 模型查詢構造器分頁方法一致：

```php
fastPaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null);
simpleFastPaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null);
```

- `$perPage`：每頁條目數；`null` 使用模型配置的每頁條目數。
- `$columns`：獲取完整數據時查詢的列。
- `$pageName`：用於解析當前頁的查詢字符串參數名。
- `$page`：明確指定的頁碼；`null` 時從當前請求解析。

`fastPaginate()` 返回包含總數的長度感知分頁器。`simpleFastPaginate()` 返回僅判斷是否還有下一頁的簡單
分頁器。`BelongsToMany` 關係會保留已填充的中間表數據，同時也支持 `HasManyThrough` 關係。

### Scout 查詢構造器

安裝 `hyperf/scout` 後，組件還會在 `Hyperf\Scout\Builder` 上註冊 `fastPaginate()`：

```php
User::search('Hyperf')->fastPaginate();
```

其簽名為 `fastPaginate($perPage = null, $pageName = 'page', $page = null)`。該 Scout 宏會直接調用
Scout 的標準 `paginate()` 方法，不使用數據庫兩次查詢優化。本組件不強制依賴 `hyperf/scout`。

## 自動回退

當查詢結構與優化方式不兼容時，組件會自動調用對應的標準 `paginate()` 或 `simplePaginate()` 方法。
以下情況會觸發回退：

- 查詢包含 `having`、`group by` 或 `union` 子句；
- `$perPage` 為 `-1`；
- 排序所需的選中表達式包含 `?` 綁定佔位符。

這些回退會保留分頁行為，但不會獲得快速分頁優化。
