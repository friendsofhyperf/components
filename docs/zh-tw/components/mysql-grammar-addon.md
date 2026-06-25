# MySQL Grammar Addon

該元件用於避免 Hyperf 讀取 MySQL 表結構元資料時，非 ASCII 欄位註釋出現亂碼。

## 安裝

```shell
composer require friendsofhyperf/mysql-grammar-addon --dev
```

該包要求 `hyperf/database` 和 `hyperf/di` 的版本為 `~3.2.0`，且未宣告可選依賴。Hyperf 包
自動發現會自動註冊該元件的切面，無需額外配置。

## 行為

該切面會攔截以下 MySQL 表結構語法方法：

- `Hyperf\Database\Schema\Grammars\MySqlGrammar::compileColumnListing()`
- `Hyperf\Database\Schema\Grammars\MySqlGrammar::compileColumns()`

它會修改生成的元資料查詢，以二進位制形式讀取欄位註釋：

```sql
binary `column_comment`
```

這樣，讀取 MySQL 表結構元資料的程式碼可以保留註釋的原始位元組。該元件不會新增查詢構造器方法，
也沒有需要應用程式碼呼叫的公開 API。

## 示例

安裝該元件之前，生成的模型註解可能包含亂碼：

```php
/**
 * @property int $user_id ??id
 * @property string $event_name ????
 */
```

安裝該元件之後，可以保留原始註釋：

```php
/**
 * @property int $user_id 使用者id
 * @property string $event_name 事件名稱
 */
```
