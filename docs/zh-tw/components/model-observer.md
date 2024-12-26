# Model Observer

The model observer component for Hyperf.

## 安裝

```shell
composer require friendsofhyperf/model-observer
```

## 用法

- 透過命令生成

```shell
php bin/hyperf.php gen:observer TestObserver --model="App\\Model\\User"
```

- 單個繫結

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

- 多個繫結

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

- 繫結到模型

```php
namespace App\Model;

use App\Observer\FooObserver;

#[ObservedBy(FooObserver::class)]
class User extends Model
{
    // ...
}
```

## 支援的事件

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
