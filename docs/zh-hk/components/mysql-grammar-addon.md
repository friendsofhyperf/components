# MySQL Grammar Addon

此組件用於避免 Hyperf 讀取 MySQL 資料表結構元數據時，非 ASCII 欄位註釋出現亂碼。

## 安裝

```shell
composer require friendsofhyperf/mysql-grammar-addon --dev
```

此套件要求 `hyperf/database` 和 `hyperf/di` 的版本為 `~3.2.0`，且未聲明可選依賴。Hyperf
套件自動發現會自動註冊此組件的切面，無需額外設定。

## 行為

此切面會攔截以下 MySQL 資料表結構語法方法：

- `Hyperf\Database\Schema\Grammars\MySqlGrammar::compileColumnListing()`
- `Hyperf\Database\Schema\Grammars\MySqlGrammar::compileColumns()`

它會修改產生的元數據查詢，以二進制形式讀取欄位註釋：

```sql
binary `column_comment`
```

這樣，讀取 MySQL 資料表結構元數據的程式碼可以保留註釋的原始位元組。此組件不會新增查詢建構器
方法，也沒有需要應用程式碼呼叫的公開 API。

## 範例

安裝此組件之前，產生的模型註解可能包含亂碼：

```php
/**
 * @property int $user_id ??id
 * @property string $event_name ????
 */
```

安裝此組件之後，可以保留原始註釋：

```php
/**
 * @property int $user_id 用户id
 * @property string $event_name 事件名称
 */
```
