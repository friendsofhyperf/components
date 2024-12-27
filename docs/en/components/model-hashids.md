# Model Hashids

Using hashids instead of integer IDs in URLs and list items can be more attractive and elegant. For more information, please visit [hashids.org](https://hashids.org/).

This package adds hashids to Hyperf models by dynamically encoding/decoding them, instead of persisting them to the database. Therefore, no extra database columns are required, and better performance can be achieved by using primary keys in queries.

Features include:

- Generating hashids for models
- Resolving hashids to models
- Ability to customize hashid settings for each model
- Using hashids for route model binding (optional)

## Installation

```shell
composer require friendsofhyperf/model-hashids
```

Additionally, publish the vendor configuration file to your application (required by the dependency):

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/model-hashids
```

## Configuration

Basic functionality is provided by using the `HasHashid` trait, and hashid-based route model binding can be enabled by using the `HashidRouting` trait.

```php
use Hyperf\Database\Model\Model;
use FriendsOfHyperf\ModelHashids\Concerns\HasHashid;
use FriendsOfHyperf\ModelHashids\Concerns\HashidRouting;

class Item extends Model
{
    use HasHashid, HashidRouting;
}
```

### Customizing Hashid Settings

Hashid settings can be customized for each model by overriding the `getHashidsConnection()` method. It must return the name of the connection in `config/autoload/hashids.php`.

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

// Decoding a hashid to its equivalent ID
$item->hashidToId($hashid);

// Encoding an ID to its equivalent hashid
$item->idToHashid($id);

// Getting the name of the hashid connection
$item->getHashidsConnection();
```

### Adding the Hashid to the Serialized Model

To set it as a default:

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

To set it for specific routes:

```php
return $item->append('hashid')->toJson();
```

### Implicit Route Model Binding

If you want to resolve hashid values for models in implicit route model bindings, you can use the `HashidRouting` trait in the model.

```php
use Hyperf\Database\Model\Model;
use FriendsOfHyperf\ModelHashids\Concerns\HasHashid;
use FriendsOfHyperf\ModelHashids\Concerns\HashidRouting;

class Item extends Model
{
    use HasHashid, HashidRouting;
}
```

This trait overrides the `getRouteKeyName()`, `getRouteKey()`, and `resolveRouteBindingQuery()` methods to use hashids as the route key.

It supports Laravel’s feature for specifying custom route keys.

```php
Route::get('/items/{item:slug}', function (Item $item) {
    return $item;
});
```

#### Customizing the Default Route Key Name

If you want to use a different field as the default for implicit route model bindings, you can override the `getRouteKeyName()` method to return the field name used in the resolution process, and the `getRouteKey()` method to return its value in links.

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

You can still specify hashid for particular routes:

```php
Route::get('/items/{item:hashid}', function (Item $item) {
    return $item;
});
```

#### Supporting Laravel’s Additional Implicit Route Model Binding Features

When using `HashidRouting`, features such as soft deletes and scoped route bindings are also supported.

```php
Route::get('/items/{item}', function (Item $item) {
    return $item;
})->withTrashed();

Route::get('/user/{user}/items/{item}', function (User $user, Item $item) {
    return $item;
})->scopeBindings();
```