# Cache

## 安装

```shell
composer require friendsofhyperf/cache
```

## 用法

### 注解

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

### 门面

```php
use FriendsOfHyperf\Cache\Facade\Cache;

Cache::remember($key, $ttl=60, function() {
    // return sth
});
```

### 切换驱动

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

## 参考

Likes [Laravel-Cache](https://laravel.com/docs/8.x/cache)
