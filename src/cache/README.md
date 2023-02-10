# Cache

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/cache)](https://packagist.org/packages/friendsofhyperf/cache)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/cache)](https://packagist.org/packages/friendsofhyperf/cache)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/cache)](https://github.com/friendsofhyperf/cache)

The cache component for Hyperf.

## Installation

- Request

```bash
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

## Sponsor

If you like this project, Buy me a cup of coffee. [ [Alipay](https://hdj.me/images/alipay.jpg) | [WePay](https://hdj.me/images/wechat-pay.jpg) ]
