# Cache

## 簡介

`friendsofhyperf/cache` 是一個基於 `hyperf/cache` 的元件。 提供更多簡潔性的擴充套件方法

## 安裝

```shell
composer require friendsofhyperf/cache
```

## 用法

### 註解

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
            // return sth
        });
    }
}
```

### 門面

```php
use FriendsOfHyperf\Cache\Facade\Cache;

Cache::remember($key, $ttl=60, function() {
    // return sth
});
```

### 切換驅動

```php
use FriendsOfHyperf\Cache\Facade\Cache;
use FriendsOfHyperf\Cache\CacheManager;

Cache::store('co')->remember($key, $ttl=60, function() {
    // return sth
});

di(CacheManager::class)->store('co')->remember($key, $ttl=60, function() {
    // return sth
});
```

## 參考

Likes [Laravel-Cache](https://laravel.com/docs/8.x/cache)
