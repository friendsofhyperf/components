# Model Observer

[English](README.md)

Model Observer 组件用于注册处理 Hyperf 模型事件的类。观察者可以在观察者类上绑定，也可以在模型类上绑定。

## 安装

```shell
composer require friendsofhyperf/model-observer
```

组件会通过 ConfigProvider 自动注册。与 Hyperf 模型一起使用时需要安装 `hyperf/database`：

```shell
composer require hyperf/database
```

## 生成观察者

向 `gen:observer` 传入模型类，可在默认的 `App\Observer` 命名空间中生成观察者：

```shell
php bin/hyperf.php gen:observer UserObserver --model="App\\Model\\User"
```

该命令还支持：

```shell
php bin/hyperf.php gen:observer UserObserver \
  --model="App\\Model\\User" \
  --namespace="App\\ModelObserver" \
  --force
```

- `--model`、`-M`：用于生成属性和方法签名的模型类。
- `--namespace`、`-N`：生成的观察者命名空间。
- `--force`、`-f`：覆盖已存在的观察者文件。

可以在 `config/autoload/devtool.php` 中修改默认命名空间或模板：

```php
return [
    'generator' => [
        'observer' => [
            'namespace' => 'App\\ModelObserver',
            'stub' => BASE_PATH . '/stubs/observer.stub',
        ],
    ],
];
```

## 从观察者绑定

在观察者类上使用 `#[Observer]`。`model` 参数接受一个模型类或模型类数组：

```php
namespace App\Observer;

use App\Model\User;
use FriendsOfHyperf\ModelObserver\Annotation\Observer;

#[Observer(model: User::class)]
class UserObserver
{
    public function creating(User $model): void
    {
        // 创建用户前执行。
    }

    public function created(User $model): void
    {
        // 创建用户后执行。
    }
}
```

```php
namespace App\Observer;

use App\Model\Post;
use App\Model\User;
use FriendsOfHyperf\ModelObserver\Annotation\Observer;
use Hyperf\Database\Model\Model;

#[Observer(model: [User::class, Post::class])]
class SearchIndexObserver
{
    public function saved(Model $model): void
    {
        // 更新搜索索引。
    }
}
```

`#[Observer]` 可以重复声明。使用 `priority` 可让优先级较高的观察者先执行：

```php
#[Observer(model: User::class, priority: 100)]
#[Observer(model: Post::class, priority: 50)]
class AuditObserver
{
    // ...
}
```

## 从模型绑定

使用 `#[ObservedBy]` 在模型上声明观察者。它的 `classes` 参数接受一个观察者类或观察者类数组，
并且该属性可以重复声明：

```php
namespace App\Model;

use App\Observer\AuditObserver;
use App\Observer\UserObserver;
use FriendsOfHyperf\ModelObserver\Annotation\ObservedBy;
use Hyperf\Database\Model\Model;

#[ObservedBy([UserObserver::class, AuditObserver::class], priority: 100)]
class User extends Model
{
    // ...
}
```

来自 `#[Observer]` 和 `#[ObservedBy]` 的绑定会合并。同一模型的重复观察者类只会调用一次。

## 在协程中运行观察者

如果观察者上所有可调用的事件方法都应在新协程中运行，请实现 `ShouldCoroutine`：

```php
namespace App\Observer;

use App\Model\User;
use FriendsOfHyperf\ModelObserver\Annotation\Observer;
use FriendsOfHyperf\ModelObserver\Contract\ShouldCoroutine;

#[Observer(model: User::class)]
class UserObserver implements ShouldCoroutine
{
    public function created(User $model): void
    {
        // 在新创建的协程中运行。
    }
}
```

协程观察者异步运行，不要依赖它们在模型操作返回前执行完毕。

## 支持的事件

观察者只需定义需要的方法。每个可调用方法都会接收模型实例：

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

组件通过 Hyperf 事件分发这些方法。观察者方法的返回值会被忽略，不能用于取消模型操作。
