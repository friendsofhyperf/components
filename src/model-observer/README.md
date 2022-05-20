# model-observer

[![Latest Stable Version](https://poser.pugx.org/friendsofhyperf/model-observer/version.png)](https://packagist.org/packages/friendsofhyperf/model-observer)
[![Total Downloads](https://poser.pugx.org/friendsofhyperf/model-observer/d/total.png)](https://packagist.org/packages/friendsofhyperf/model-observer)
[![GitHub license](https://img.shields.io/github/license/friendsofhyperf/model-observer)](https://github.com/friendsofhyperf/model-observer)

## Installation

```bash
composer require friendsofhyperf/model-observer
```

## Usage

- Generator command

```bash
php bin/hyperf.php gen:observer TestObserver --model="App\\Model\\User"
```

- Custom

```php
namespace App\Observer;

use App\Model\User;
use FriendsOfHyperf\ModelObserver\Annotation\Observer;

/**
 * @Observer(User::class)
 */
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
