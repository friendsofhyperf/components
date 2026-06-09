# MySQL Grammar Addon

[English](README.md)

该组件用于避免 Hyperf 读取 MySQL 表结构元数据时，非 ASCII 字段注释出现乱码。

## 安装

```shell
composer require friendsofhyperf/mysql-grammar-addon --dev
```

该包要求 `hyperf/database` 和 `hyperf/di` 的版本为 `~3.2.0`，且未声明可选依赖。Hyperf 包
自动发现会自动注册该组件的切面，无需额外配置。

## 行为

该切面会拦截以下 MySQL 表结构语法方法：

- `Hyperf\Database\Schema\Grammars\MySqlGrammar::compileColumnListing()`
- `Hyperf\Database\Schema\Grammars\MySqlGrammar::compileColumns()`

它会修改生成的元数据查询，以二进制形式读取字段注释：

```sql
binary `column_comment`
```

这样，读取 MySQL 表结构元数据的代码可以保留注释的原始字节。该组件不会添加查询构造器方法，
也没有需要应用代码调用的公开 API。

## 示例

安装该组件之前，生成的模型注解可能包含乱码：

```php
/**
 * @property int $user_id ??id
 * @property string $event_name ????
 */
```

安装该组件之后，可以保留原始注释：

```php
/**
 * @property int $user_id 用户id
 * @property string $event_name 事件名称
 */
```
