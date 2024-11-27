# Compoships

**Compoships** 提供了在 Hyperf 的 Model ORM 中基于两个（或更多）列指定关系的能力。当处理第三方或预先存在的模式/数据库时，通常会出现需要在 Eloquent 关系的定义中匹配多个列的情况。

## The problem

Eloquent 不支持复合键。因此，无法通过匹配多个列来定义从一个模型到另一个模型的关系。尝试使用 `where` 子句（如下例所示）在预加载关系时不起作用，因为在处理关系时 **$this->team_id** 为 null。

```php
namespace App;

use Hyperf\Database\Model\Model;

class User extends Model
{
    public function tasks()
    {
        //WON'T WORK WITH EAGER LOADING!!!
        return $this->hasMany(Task::class)->where('team_id', $this->team_id);
    }
}
```

## 安装

推荐的安装 **Compoships** 的方法是通过 [Composer](http://getcomposer.org/)

```shell
composer require friendsofhyperf/compoships
```

## 使用

### 使用 `FriendsOfHyperf\Compoships\Database\Eloquent\Model` 类

只需让您的模型类派生自 `FriendsOfHyperf\Compoships\Database\Eloquent\Model` 基类。`FriendsOfHyperf\Compoships\Database\Eloquent\Model` 扩展了 `Eloquent` 基类，而不改变其核心功能。

### 使用 `FriendsOfHyperf\Compoships\Compoships` trait

如果由于某些原因您无法从 `FriendsOfHyperf\Compoships\Database\Eloquent\Model` 派生您的模型，您可以利用 `FriendsOfHyperf\Compoships\Compoships` trait。只需在您的模型中使用该 trait。

**注意：** 要从模型 *A* 到另一个模型 *B* 定义多列关系，**两个模型都必须扩展 `FriendsOfHyperf\Compoships\Database\Eloquent\Model` 或使用 `FriendsOfHyperf\Compoships\Compoships` trait**

### 用法

... 现在我们可以通过匹配两个或更多列（通过传递列数组而不是字符串）来定义从模型 *A* 到另一个模型 *B* 的关系。

```php
namespace App;

use Hyperf\Database\Model\Model;

class A extends Model
{
    use \FriendsOfHyperf\Compoships\Compoships;
    
    public function b()
    {
        return $this->hasMany('B', ['foreignKey1', 'foreignKey2'], ['localKey1', 'localKey2']);
    }
}
```

我们可以使用相同的语法来定义关系的反向关系：

```php
namespace App;

use Hyperf\Database\Model\Model;

class B extends Model
{
    use \FriendsOfHyperf\Compoships\Compoships;
    
    public function a()
    {
        return $this->belongsTo('A', ['foreignKey1', 'foreignKey2'], ['ownerKey1', 'ownerKey2']);
    }
}
```

### 例子

作为一个例子，假设我们有一个带有分类的任务列表，由多个用户团队管理，其中：

- 一个任务属于一个分类
- 一个任务被分配给一个团队
- 一个团队有很多用户
- 一个用户属于一个团队
- 一个用户负责一个分类的任务

负责特定任务的用户是当前负责团队内该分类的用户。

```php
namespace App;

use Hyperf\Database\Model\Model;

class User extends Model
{
    use \FriendsOfHyperf\Compoships\Compoships;
    
    public function tasks()
    {
        return $this->hasMany(Task::class, ['team_id', 'category_id'], ['team_id', 'category_id']);
    }
}
```

同样的语法可以定义关系的反向关系：

```php
namespace App;

use Hyperf\Database\Model\Model;

class Task extends Model
{
    use \FriendsOfHyperf\Compoships\Compoships;
    
    public function user()
    {
        return $this->belongsTo(User::class, ['team_id', 'category_id'], ['team_id', 'category_id']);
    }
}
```
