# MySQL Grammar Addon

此元件用於避免 Hyperf 讀取 MySQL 資料表結構中繼資料時，非 ASCII 欄位註解出現亂碼。

## 安裝

```shell
composer require friendsofhyperf/mysql-grammar-addon --dev
```

此套件要求 `hyperf/database` 和 `hyperf/di` 的版本為 `~3.2.0`，且未宣告選用依賴。Hyperf
套件自動探索會自動註冊此元件的切面，無需額外設定。

## 行為

此切面會攔截以下 MySQL 資料表結構語法方法：

- `Hyperf\Database\Schema\Grammars\MySqlGrammar::compileColumnListing()`
- `Hyperf\Database\Schema\Grammars\MySqlGrammar::compileColumns()`

它會修改產生的中繼資料查詢，以二進位形式讀取欄位註解：

```sql
binary `column_comment`
```

如此一來，讀取 MySQL 資料表結構中繼資料的程式碼可以保留註解的原始位元組。此元件不會新增查詢
建構器方法，也沒有需要應用程式碼呼叫的公開 API。

## 範例

安裝此元件之前，產生的模型註解可能包含亂碼：

```php
/**
 * @property int $user_id ??id
 * @property string $event_name ????
 */
```

安裝此元件之後，可以保留原始註解：

```php
/**
 * @property int $user_id 用户id
 * @property string $event_name 事件名称
 */
```
