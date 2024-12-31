# Cache

## Introduction

`friendsofhyperf/cache` is a component based on `hyperf/cache`. It provides more concise extension methods.

## Installation

```shell
composer require friendsofhyperf/cache
```

## Usage

### Annotation

```php
namespace App\Controller;

use FriendsOfHyperf\Cache\Contract\CacheInterface;
use Hyperf\Di\Annotation\Inject;

class IndexController
{
   
    #[Inject]
    private CacheInterface $cache;

    public function index()
    {
        return $this->cache->remember($key, $ttl=60, function() {
            // Return value
        });
    }
}
```

### Facade

```php
use FriendsOfHyperf\Cache\Facade\Cache;

Cache::remember($key, $ttl=60, function() {
    // Return value
});
```

### Switching Drivers

```php
use FriendsOfHyperf\Cache\Facade\Cache;
use FriendsOfHyperf\Cache\CacheManager;

Cache::store('co')->remember($key, $ttl=60, function() {
    // Return value
});

di(CacheManager::class)->store('co')->remember($key, $ttl=60, function() {
    // Return value
});
```

## Reference

For more information, please refer to [Laravel-Cache](https://laravel.com/docs/8.x/cache)