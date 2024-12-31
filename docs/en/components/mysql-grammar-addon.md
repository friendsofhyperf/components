# Mysql Grammar Addon

MySqlGrammar extension component for the Hyperf framework.

## Installation

```shell
composer require friendsofhyperf/mysql-grammar-addon --dev
```

## Before Usage

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

## After Usage

```php
/**
 * @property int $id 
 * @property int $user_id User ID
 * @property string $group_name Event Group
 * @property string $event_name Event Name
 * @property string $page_name Page
 * @property string $extra Additional Information
 * @property string $device pc,android,ios,touch
 * @property string $device_id Device ID
 * @property \Carbon\Carbon $created_at Creation Time
 */
class Event extends Model
{}
```