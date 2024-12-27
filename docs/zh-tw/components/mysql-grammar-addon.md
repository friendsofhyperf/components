# Mysql Grammar Addon

Hyperf 框架的 MySqlGrammar 擴充套件元件。

## 安裝

```shell
composer require friendsofhyperf/mysql-grammar-addon --dev
```

## 使用之前

```php
/**
 * @property int $id
 * @property int $user_id ??id
 * @property string $group_name ????
 * @property string $event_name ????
 * @property string $page_name ??
 * @property string $extra ????
 * @property string $device pc,android,ios,touch
 * @property string $device_id ???
 * @property \Carbon\Carbon $created_at ????
 */
class Event extends Model
{}
```

## 使用之後

```php
/**
 * @property int $id 
 * @property int $user_id 使用者id
 * @property string $group_name 事件分組
 * @property string $event_name 事件名稱
 * @property string $page_name 頁面
 * @property string $extra 額外資訊
 * @property string $device pc,android,ios,touch
 * @property string $device_id 裝置號
 * @property \Carbon\Carbon $created_at 建立時間
 */
class Event extends Model
{}
```
