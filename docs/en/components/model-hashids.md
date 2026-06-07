# Model Hashids

Model Hashids encodes and decodes a model's primary key on demand. The hashid is not stored in the
database, so queries still use the model's primary-key column.

## Installation

```shell
composer require friendsofhyperf/model-hashids
```

The package requires `hashids/hashids` and Hyperf 3.2's config, database, and stringable components.
Publish the configuration file only when you need to customize the hashid settings:

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/model-hashids
```

## Setup

Add `HasHashid` to a model. Add `HashidRouting` as well if implicit route binding should use hashids:

```php
use FriendsOfHyperf\ModelHashids\Concerns\HasHashid;
use FriendsOfHyperf\ModelHashids\Concerns\HashidRouting;
use Hyperf\Database\Model\Model;

class Item extends Model
{
    use HasHashid;
    use HashidRouting;
}
```

## Configuration

The published file is `config/autoload/hashids.php`. `default` selects a connection, and each
connection accepts `salt`, `length`, and `alphabet`:

```php
return [
    'default' => 'main',
    'connections' => [
        'main' => [
            'salt' => '',
            'length' => 0,
            'alphabet' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890',
        ],
    ],
];
```

Without a published configuration, the component uses the `main` connection, an empty salt, a
minimum length of `0`, and the alphabet shown above.

To select a different connection for one model, override the protected `getHashidsConnection()`
method:

```php
class Item extends Model
{
    use HasHashid;

    protected function getHashidsConnection()
    {
        return 'alternative';
    }
}
```

## Usage

### Encoding and Queries

```php
// Encode the model's primary key.
$item->hashid();

// Access the same value through the hashid accessor.
$item->hashid;

// Encode an ID or decode a valid hashid.
$item->idToHashid($id);
$item->hashidToId($hashid);

// Add a primary-key constraint decoded from the hashid.
Item::query()->byHashid($hashid)->get();

// Return the first matching model, null, or throw ModelNotFoundException.
Item::findByHashid($hashid);
Item::findByHashidOrFail($hashid);
```

`hashidToId()` returns the first ID decoded by `hashids/hashids`; pass it a valid hashid created
with the same connection settings.

### Serializing the Hashid

The `hashid` accessor is not appended automatically. Add it to the model's `$appends` property:

```php
class Item extends Model
{
    use HasHashid;

    protected $appends = ['hashid'];
}
```

Alternatively, append it only when needed:

```php
return $item->append('hashid')->toJson();
```

### Implicit Route Binding

`HashidRouting` makes the default route key the model's hashid and resolves it through `byHashid`:

```php
Route::get('/items/{item}', function (Item $item) {
    return $item;
});
```

For a route that explicitly names another field, resolution is delegated to the model's parent
implementation:

```php
Route::get('/items/{item:slug}', function (Item $item) {
    return $item;
});
```

You can also make another field the default while still selecting `hashid` on a specific route:

```php
class Item extends Model
{
    use HasHashid;
    use HashidRouting;

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

```php
Route::get('/items/{item:hashid}', function (Item $item) {
    return $item;
});
```
