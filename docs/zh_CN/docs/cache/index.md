
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