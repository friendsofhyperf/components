# Model Observer

模型观察者组件，适用于 Hyperf 框架。

## 安装

```shell
composer require friendsofhyperf/model-observer
```

## 用法

- 通过命令生成

```shell
php bin/hyperf.php gen:observer TestObserver --model="App\\Model\\User"
```

- 单个绑定

```php
namespace App\Observer;

use App\Model\User;
use FriendsOfHyperf\ModelObserver\Annotation\Observer;

#[Observer(model: User::class)]
class FooObserver
{
    public function creating(User $model)
    {
        // do sth...
    }

    public function created(User $model)
    {
        // do sth...
    }

    // another events
}
```

- 多个绑定

```php
namespace App\Observer;

use App\Model\Post;
use App\Model\User;
use FriendsOfHyperf\ModelObserver\Annotation\Observer;

#[Observer(model: [User::class, Post::class])]
class FooObserver
{
    public function creating($model)
    {
        // do sth...
    }

    public function created($model)
    {
        // do sth...
    }

    // another events
}
```

- 绑定到模型

```php
namespace App\Model;

use App\Observer\FooObserver;

#[ObservedBy(FooObserver::class)]
class User extends Model
{
    // ...
}
```

## 支持的事件

- `booting`
- `booted`
- `retrieved`
- `creating`
- `created`
- `updating`
- `updated`
- `saving`
- `saved`
- `restoring`
- `restored`
- `deleting`
- `deleted`
- `forceDeleted`
