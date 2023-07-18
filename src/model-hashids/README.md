# model-hashids

> Using hashids instead of integer ids in urls and list items can be more
appealing and clever. For more information visit [hashids.org](https://hashids.org/).

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/model-hashids)](https://packagist.org/packages/friendsofhyperf/model-hashids)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/model-hashids)](https://packagist.org/packages/friendsofhyperf/model-hashids)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/model-hashids)](https://github.com/friendsofhyperf/model-hashids)

This adds hashids to Hyperf models by encoding/decoding them on the fly rather than persisting them in the database. So no need for another database column and also higher performance by using primary keys in queries.

Features include:

* Generating hashids for models
* Resloving hashids to models
* Ability to customize hashid settings for each model
* Route binding with hashids (optional)

## Installation

Install the package with Composer:

```shell
composer require friendsofhyperf/model-hashids
```

Also, publish the vendor config files to your application (necessary for the dependencies):

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/model-hashids
```

## Setup

Base features are provided by using `HasHashid` trait then route binding with hashids can be added by using `HashidRouting`.

```php

use Hyperf\Database\Model\Model;
use FriendsOfHyperf\ModelHashids\Concerns\HasHashid;
use FriendsOfHyperf\ModelHashids\Concerns\HashidRouting;

Class Item extends Model
{
    use HasHashid, HashidRouting;
}

```

### Custom Hashid Settings

It's possible to customize hashids settings for each model by overwriting `getHashidsConnection()`. It must return the name of a connection of `config/autoload/hashids.php`.

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

### Add the hashid to the serialized model

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

or specify it specificly:

`return $item->append('hashid')->toJson();`

### Implicit Route Bindings

If you want to resolve implicit route bindings for the model using its hahsid value you can use `HashidRouting` in the model.

```php

use Hyperf\Database\Model\Model;
use FriendsOfHyperf\ModelHashids\Concerns\HasHashid;
use FriendsOfHyperf\ModelHashids\Concerns\HashidRouting;

class Item extends Model
{
    use HasHashid, HashidRouting;
}

```

It overwrites `getRouteKeyName()`, `getRouteKey()` and `resolveRouteBindingQuery()` to use the hashids as the route keys.

It supports the Laravel's feature for customizing the key for specific routes.

```php

Route::get('/items/{item:slug}', function (Item $item) {
    return $item;
});

```

#### Customizing The Default Route Key Name

If you want to by default resolve the implicit route bindings using another field you can overwrite `getRouteKeyName()` to return the field's name to the resolving process and `getRouteKey()` to return its value in your links.

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

You'll still be able to specify the hashid for specific routes.

```php

Route::get('/items/{item:hashid}', function (Item $item) {
    return $item;
});

```

#### Supporting The Other Laravel's Implicit Route Binding Features

When using `HashidRouting` you'll still be able to use softdeletable and child route bindings.

```php

Route::get('/items/{item}', function (Item $item) {
    return $item;
})->withTrashed();

Route::get('/user/{user}/items/{item}', function (User $user, Item $item) {
    return $item;
})->scopeBindings();

```

## Donate

> If you like them, Buy me a cup of coffee.

| Alipay | WeChat | Buy Me A Coffee |
|  ----  |  ----  |  ----  |
| <img src="https://hdj.me/images/alipay-min.jpg" width="200" height="200" />  | <img src="https://hdj.me/images/wechat-pay-min.jpg" width="200" height="200" /> | <img src="https://hdj.me/images/bmc_qr.jpg" width="200" height="200" /> |

<a href="https://www.buymeacoffee.com/huangdijiag" target="_blank"><img src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" alt="Buy Me A Coffee" style="height: 60px !important;width: 217px !important;" ></a>

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
