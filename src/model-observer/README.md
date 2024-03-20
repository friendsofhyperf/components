# model-observer

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/model-observer)](https://packagist.org/packages/friendsofhyperf/model-observer)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/model-observer)](https://packagist.org/packages/friendsofhyperf/model-observer)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/model-observer)](https://github.com/friendsofhyperf/model-observer)

The model observer component for Hyperf.

## Installation

```shell
composer require friendsofhyperf/model-observer
```

## Usage

- Generator command

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

- Multiple binding

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

- Binding on model

```php
namespace App\Model;

use App\Observer\FooObserver;

#[ObservedBy(FooObserver::class)]
class User extends Model
{
    // ...
}
```

## Methods

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

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
