# Compoships

**Compoships** 允许 Hyperf 的 Model ORM 定义基于两个或更多列匹配的模型关系。
它适用于第三方或遗留数据库结构中单个外键列不足以定位关联记录的场景。

Compoships 只扩展关系处理能力；它不会让 Hyperf 模型自身的主键变成复合主键。

## 问题

Eloquent 风格的关系通常只会把一个外键列匹配到一个本地键或拥有者键列。在关系中
额外添加 `where` 子句并不能替代复合键关系，因为预加载会在单个模型实例可用之前
构造关系查询。下面的示例中，准备预加载约束时 `$this->team_id` 是 `null`。

```php
namespace App;

use Hyperf\Database\Model\Model;

class User extends Model
{
    public function tasks()
    {
        return $this->hasMany(Task::class)->where('team_id', $this->team_id);
    }
}
```

## 安装

通过 Composer 安装组件：

```shell
composer require friendsofhyperf/compoships
```

该组件面向 Hyperf 3.2，并依赖组件声明的 `hyperf/database` 及相关 Hyperf 支撑包。
`composer.json` 中的建议包是可选第三方包，并不是 Compoships 关系所必需的依赖。

## 配置

无需发布配置文件。组件提供了 Hyperf `ConfigProvider`，但当前返回的是空配置数组。

## 使用

### 使用 `FriendsOfHyperf\Compoships\Database\Eloquent\Model` 类

让模型继承 `FriendsOfHyperf\Compoships\Database\Eloquent\Model`，而不是直接继承
`Hyperf\Database\Model\Model`。这个基础模型使用了 Compoships trait，同时保留正常
的 Hyperf 模型行为。

```php
namespace App;

use FriendsOfHyperf\Compoships\Database\Eloquent\Model;

class User extends Model
{
}
```

### 使用 `FriendsOfHyperf\Compoships\Compoships` Trait

如果模型必须继承其他基础类，可以在模型中使用
`FriendsOfHyperf\Compoships\Compoships` trait。

```php
namespace App;

use FriendsOfHyperf\Compoships\Compoships;
use Hyperf\Database\Model\Model;

class User extends Model
{
    use Compoships;
}
```

当关系使用键名数组时，被关联模型也必须接入 Compoships：要么继承
`FriendsOfHyperf\Compoships\Database\Eloquent\Model`，要么使用
`FriendsOfHyperf\Compoships\Compoships` trait。否则定义关系时会抛出
`FriendsOfHyperf\Compoships\Exceptions\InvalidUsageException`。

## 关系语法

Compoships 支持在以下关系方法中使用复合键：

- `hasOne($related, $foreignKey = null, $localKey = null)`
- `hasMany($related, $foreignKey = null, $localKey = null)`
- `belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null)`

将键参数从字符串改为数组即可。数组顺序和元素数量应保持一致，因为值会按数组下标
逐项匹配。

对 `hasOne` 和 `hasMany` 来说，外键数组表示被关联模型上的列，本地键数组表示当前
模型上的列：

```php
namespace App;

use FriendsOfHyperf\Compoships\Compoships;
use Hyperf\Database\Model\Model;

class Team extends Model
{
    use Compoships;

    public function latestTask()
    {
        return $this->hasOne(Task::class, ['team_id', 'category_id'], ['id', 'category_id']);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, ['team_id', 'category_id'], ['id', 'category_id']);
    }
}
```

对 `belongsTo` 来说，外键数组表示当前模型上的列，拥有者键数组表示被关联模型上的
列：

```php
namespace App;

use FriendsOfHyperf\Compoships\Compoships;
use Hyperf\Database\Model\Model;

class Task extends Model
{
    use Compoships;

    public function team()
    {
        return $this->belongsTo(Team::class, ['team_id', 'category_id'], ['id', 'category_id']);
    }
}
```

## 示例

假设一个任务列表由多个团队管理，并且每个团队中每个任务分类都有一名负责用户：

- 一个任务属于一个分类。
- 一个任务被分配给一个团队。
- 一个团队有多个用户。
- 一个用户属于一个团队。
- 一个用户负责一个分类下的任务。

某个任务的负责用户，就是该任务所属团队中负责该任务分类的用户。

```php
namespace App;

use FriendsOfHyperf\Compoships\Compoships;
use Hyperf\Database\Model\Model;

class User extends Model
{
    use Compoships;

    public function tasks()
    {
        return $this->hasMany(Task::class, ['team_id', 'category_id'], ['team_id', 'category_id']);
    }
}
```

反向关系使用同一组列：

```php
namespace App;

use FriendsOfHyperf\Compoships\Compoships;
use Hyperf\Database\Model\Model;

class Task extends Model
{
    use Compoships;

    public function user()
    {
        return $this->belongsTo(
            User::class,
            ['team_id', 'category_id'],
            ['team_id', 'category_id']
        );
    }
}
```

## 行为说明

Compoships 使用自定义查询构造器，因此预加载可以应用多列 `whereIn` 约束，关系存在性
查询也可以通过 `whereColumn` 比较多列。

对 `hasOne` 和 `hasMany` 来说，`save()` 和 `create()` 会把父模型本地键的值按顺序
写入被关联模型的各个外键列。对 `belongsTo` 来说，`associate()` 会把拥有者键的值
按顺序写入当前模型的各个外键列。
