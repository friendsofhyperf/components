# mysql-grammar-addon

The MySqlGrammar addon for Hyperf.

## 安装

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

## 使用之后

```php
/**
 * @property int $id 
 * @property int $user_id 用户id
 * @property string $group_name 事件分组
 * @property string $event_name 事件名称
 * @property string $page_name 页面
 * @property string $extra 额外信息
 * @property string $device pc,android,ios,touch
 * @property string $device_id 设备号
 * @property \Carbon\Carbon $created_at 创建时间
 */
class Event extends Model
{}
```
