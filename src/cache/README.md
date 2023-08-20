# Cache

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/cache)](https://packagist.org/packages/friendsofhyperf/cache)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/cache)](https://packagist.org/packages/friendsofhyperf/cache)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/cache)](https://github.com/friendsofhyperf/cache)

The cache component for Hyperf.

## Installation

- Installation

```shell
composer require friendsofhyperf/cache
```

## Usage

- Inject

```php
namespace App\Controller;

use FriendsOfHyperf\Cache\CacheInterface;
use Hyperf\Di\Annotation\Inject;

class IndexController
{
   
    #[Inject]
    private CacheInterface $cache;

    public function index()
    {
        return $this->cache->remember($key, $ttl=60, function() {
            // return sth
        });
    }
}
```

- Facade

```php
use FriendsOfHyperf\Cache\Facade\Cache;

Cache::remember($key, $ttl=60, function() {
    // return sth
});
```

- Switch driver

```php
use FriendsOfHyperf\Cache\Facade\Cache;
use FriendsOfHyperf\Cache\CacheManager;

Cache::driver('co')->remember($key, $ttl=60, function() {
    // return sth
});

CacheManager::get('co')->remember($key, $ttl=60, function() {
    // return sth
});
```

## Methods

Likes [Laravel-Cache](https://laravel.com/docs/8.x/cache)

## Donate

> If you like them, Buy me a cup of coffee.

| Alipay | WeChat |
|  ----  |  ----  |
| <img src="https://hdj.me/images/alipay-min.jpg" width="200" height="200" />  | <img src="https://hdj.me/images/wechat-pay-min.jpg" width="200" height="200" /> |

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
