# MySQL Grammar Addon

This component prevents non-ASCII MySQL column comments from becoming garbled when Hyperf reads
schema metadata.

## Installation

```shell
composer require friendsofhyperf/mysql-grammar-addon --dev
```

The package requires `hyperf/database` and `hyperf/di` version `~3.2.0` and declares no optional
dependencies. Hyperf package discovery automatically registers the component's aspect, so no
configuration is required.

## Behavior

The aspect intercepts these MySQL schema grammar methods:

- `Hyperf\Database\Schema\Grammars\MySqlGrammar::compileColumnListing()`
- `Hyperf\Database\Schema\Grammars\MySqlGrammar::compileColumns()`

It changes the generated metadata queries to select the column comment as binary:

```sql
binary `column_comment`
```

This preserves the original comment bytes for code that reads MySQL schema metadata. The component
does not add query-builder methods or expose an API that application code needs to call.

## Example

Before installing the component, a generated model annotation may contain garbled comments:

```php
/**
 * @property int $user_id ??id
 * @property string $event_name ????
 */
```

After installing the component, the original comments can be preserved:

```php
/**
 * @property int $user_id 用户id
 * @property string $event_name 事件名称
 */
```
