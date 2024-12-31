# Model Hashids

Using hashids instead of integer IDs in URLs and list items can be more attractive and clever. For more information, visit [hashids.org](https://hashids.org/).

This package adds hashids to Hyperf models by dynamically encoding/decoding them, rather than persisting them in the database. This eliminates the need for additional database columns and allows for higher performance by using primary keys in queries.

Features include:

- Generating hashids for models
- Resolving hashids to models
- Ability to customize hashid settings for each model
- Using hashids for route binding (optional)

## Installation

```shell
composer require friendsofhyperf/model-hashids
```

Additionally, publish the vendor configuration file to your application (required for dependencies):

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/model-hashids
```

## Setup

Basic functionality is provided by using the `HasHashid` trait, and then hashid-based route binding can be added by using `HashidRouting`.

```php

use Hyperf\Database\Model\Model;
use FriendsOfHyperf\ModelHashids\Concerns\HasHashid;
use FriendsOfHyperf\ModelHashids\Concerns\HashidRouting;

Class Item extends Model
{
    use HasHashid, HashidRouting;
}

```

### Customizing Hashid Settings

Hashid settings can be customized per model by overriding `getHashidsConnection()`. It must return the name of the connection in `config/autoload/hashids.php`.

## Usage

### Basics

```php

// Generating the model hashid based on its key
$item->hashid();

// Equivalent to the above but with the attribute style
$item->hashid;

// Finding a model based on the provided hashid or
// returning null on failure
Item::findByHashid($hashid);

// Finding a model based on the provided hashid or
// throwing a ModelNotFoundException on failure
Item::findByHashidOrFail($hashid);

// Decoding a hashid to its equivalent id 
$item->hashidToId($hashid);

// Encoding an id to its equivalent hashid
$item->idToHashid($id);

// Getting the name of the hashid connection
$item->getHashidsConnection();

```

### Adding Hashid to Serialized Models

Setting it as default:

```php

use Hyperf\Database\Model\Model;
use FriendsOfHyperf\ModelHashids\Concerns\HasHashid;

class Item extends Model
{
    use HasHashid;
    
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['hashid'];
}

```

Setting it for a specific route:

`return $item->append('hashid')->toJson();`

### Implicit Route Binding

If you want to resolve implicit route bindings using the model's hashid value, you can use `HashidRouting` in the model.

```php

use Hyperf\Database\Model\Model;
use FriendsOfHyperf\ModelHashids\Concerns\HasHashid;
use FriendsOfHyperf\ModelHashids\Concerns\HashidRouting;

class Item extends Model
{
    use HasHashid, HashidRouting;
}

```

It overrides `getRouteKeyName()`, `getRouteKey()`, and `resolveRouteBindingQuery()` to use hashids as the route key.

It supports Laravel's custom specific route key functionality.

```php

Route::get('/items/{item:slug}', function (Item $item) {
    return $item;
});

```

#### Customizing the Default Route Key Name

If you want to resolve implicit route bindings using another field by default, you can override `getRouteKeyName()` to return the field name during resolution and `getRouteKey()` to return its value in links.

```php

use Hyperf\Database\Model\Model;
use FriendsOfHyperf\ModelHashids\Concerns\HasHashid;
use FriendsOfHyperf\ModelHashids\Concerns\HashidRouting;

class Item extends Model
{
    use HasHashid, HashidRouting;

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getRouteKey()
    {
        return $this->slug;
    }
}

```

You can still specify hashid for specific routes.

```php

Route::get('/items/{item:hashid}', function (Item $item) {
    return $item;
});

```

#### Supporting Other Laravel Implicit Route Binding Features

When using `HashidRouting`, you can still use soft deletion and child route bindings.

```php

Route::get('/items/{item}', function (Item $item) {
    return $item;
})->withTrashed();

Route::get('/user/{user}/items/{item}', function (User $user, Item $item) {
    return $item;
})->scopeBindings();

```