# Model Observer

The model observer component for Hyperf.

## Installation

```shell
composer require friendsofhyperf/model-observer
```

## Usage

- Generate through command

```shell
php bin/hyperf.php gen:observer TestObserver --model="App\\Model\\User"
```

- Single binding

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

- Multiple bindings

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

- Bind to model

```php
namespace App\Model;

use App\Observer\FooObserver;

#[ObservedBy(FooObserver::class)]
class User extends Model
{
    // ...
}
```

## Supported Events

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
