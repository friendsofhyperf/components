# Model Hashids

Using hashids instead of integer IDs in URLs and list items can be more attractive and clever. For more information, visit [hashids.org](https://hashids.org/).

This package adds hashids to Hyperf models by dynamically encoding/decoding hashids rather than persisting them to the database. Therefore, no additional database columns are needed, and higher performance can be achieved by using primary keys in queries.

Features include:

- Generate hashids for models
- Resolve hashids to models
- Ability to customize hashid settings per model
- Route binding using hashids (optional)

## Installation

```shell
composer require friendsofhyperf/model-hashids
```

Additionally, publish the vendor configuration file to your application (required by the dependency):

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/model-hashids
```

## Setup

Basic functionality is provided through the use of the `HasHashid` trait, and then hashid-based route binding can be added by using `HashidRouting`.

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

Hashids settings can be customized per model by overriding `getHashidsConnection()`. It must return the name of a connection in `config/autoload/hashids.php`.

## Usage

### Basic

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

Set it as default:

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

Set it for specific route:

`return $item->append('hashid')->toJson();`

### Implicit Route Binding

If you want to resolve implicit route bindings using your model's hashid value, you can use `HashidRouting` in your model.

```php
use Hyperf\Database\Model\Model;
use FriendsOfHyperf\ModelHashids\Concerns\HasHashid;
use FriendsOfHyperf\ModelHashids\Concerns\HashidRouting;

class Item extends Model
{
    use HasHashid, HashidRouting;
}
```

It overrides `getRouteKeyName()`, `getRouteKey()`, and `resolveRouteBindingQuery()` to use hashids as route keys.

It supports Laravel's feature of customizing specific route keys.

```php
Route::get('/items/{item:slug}', function (Item $item) {
    return $item;
});
```

#### Customizing Default Route Key Name

If you want to use another field by default to resolve implicit route bindings, you can override `getRouteKeyName()` to return the field name to use in the resolution process and `getRouteKey()` to return its value in the link.

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

#### Support for Laravel's Other Implicit Route Binding Features

When using `HashidRouting`, you can still use soft deletes and child route bindings.

```php
Route::get('/items/{item}', function (Item $item) {
    return $item;
})->withTrashed();

Route::get('/user/{user}/items/{item}', function (User $user, Item $item) {
    return $item;
})->scopeBindings();
```
