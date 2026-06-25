# MySQL Grammar Addon

該組件用於避免 Hyperf 讀取 MySQL 表結構元數據時，非 ASCII 字段註釋出現亂碼。

## 安裝

```shell
composer require friendsofhyperf/mysql-grammar-addon --dev
```

該包要求 `hyperf/database` 和 `hyperf/di` 的版本為 `~3.2.0`，且未聲明可選依賴。Hyperf 包
自動發現會自動註冊該組件的切面，無需額外配置。

## 行為

該切面會攔截以下 MySQL 表結構語法方法：

- `Hyperf\Database\Schema\Grammars\MySqlGrammar::compileColumnListing()`
- `Hyperf\Database\Schema\Grammars\MySqlGrammar::compileColumns()`

它會修改生成的元數據查詢，以二進制形式讀取字段註釋：

```sql
binary `column_comment`
```

這樣，讀取 MySQL 表結構元數據的代碼可以保留註釋的原始字節。該組件不會添加查詢構造器方法，
也沒有需要應用代碼調用的公開 API。

## 示例

安裝該組件之前，生成的模型註解可能包含亂碼：

```php
/**
 * @property int $user_id ??id
 * @property string $event_name ????
 */
```

安裝該組件之後，可以保留原始註釋：

```php
/**
 * @property int $user_id 用户id
 * @property string $event_name 事件名稱
 */
```
