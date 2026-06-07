# Model Observer

The Model Observer component registers classes that handle Hyperf model events. An observer can
be bound from the observer class or from the model class.

## Installation

```shell
composer require friendsofhyperf/model-observer
```

The component is registered automatically through its ConfigProvider. It requires
`hyperf/database` when used with Hyperf models:

```shell
composer require hyperf/database
```

## Generate an Observer

Pass a model class to `gen:observer` to generate an observer in the default `App\Observer`
namespace:

```shell
php bin/hyperf.php gen:observer UserObserver --model="App\\Model\\User"
```

The command also supports:

```shell
php bin/hyperf.php gen:observer UserObserver \
  --model="App\\Model\\User" \
  --namespace="App\\ModelObserver" \
  --force
```

- `--model`, `-M`: model class used in the generated attribute and method signatures.
- `--namespace`, `-N`: namespace of the generated observer.
- `--force`, `-f`: overwrite an existing observer file.

You can change the default namespace or stub in `config/autoload/devtool.php`:

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

## Bind from an Observer

Use `#[Observer]` on an observer class. The `model` argument accepts one model class or an array of
model classes:

```php
namespace App\Observer;

use App\Model\User;
use FriendsOfHyperf\ModelObserver\Annotation\Observer;

#[Observer(model: User::class)]
class UserObserver
{
    public function creating(User $model): void
    {
        // Runs when a user is being created.
    }

    public function created(User $model): void
    {
        // Runs after a user has been created.
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
        // Update the search index.
    }
}
```

`#[Observer]` is repeatable. You can use `priority` to run higher-priority observers first:

```php
#[Observer(model: User::class, priority: 100)]
#[Observer(model: Post::class, priority: 50)]
class AuditObserver
{
    // ...
}
```

## Bind from a Model

Use `#[ObservedBy]` to declare observers on a model. Its `classes` argument accepts one observer
class or an array of observer classes, and the attribute is repeatable:

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

Bindings from `#[Observer]` and `#[ObservedBy]` are combined. Duplicate observer classes for the
same model are called only once.

## Run an Observer in a Coroutine

Implement `ShouldCoroutine` when all callable event methods on an observer should run in newly
created coroutines:

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
        // Runs in a newly created coroutine.
    }
}
```

Coroutine observers run asynchronously. Do not rely on them completing before the model operation
returns.

## Supported Events

Define only the methods needed by an observer. Each callable method receives the model instance:

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

The component dispatches these methods through Hyperf events. Return values from observer methods
are ignored and cannot cancel a model operation.
