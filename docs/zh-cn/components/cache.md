# Cache

## 简介

`friendsofhyperf/cache` 是一个基于 `hyperf/cache` 的组件。 提供更多简洁性的扩展方法。

## 安装

```shell
composer require friendsofhyperf/cache
```

## 用法

### 注解

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
            // 返回值
        });
    }
}
```

### 门面

```php
use FriendsOfHyperf\Cache\Facade\Cache;

Cache::remember($key, $ttl=60, function() {
    // 返回值
});
```

### 切换驱动

```php
use FriendsOfHyperf\Cache\Facade\Cache;
use FriendsOfHyperf\Cache\CacheManager;

Cache::store('co')->remember($key, $ttl=60, function() {
    // 返回值
});

di(CacheManager::class)->store('co')->remember($key, $ttl=60, function() {
    // 返回值
});
```

## 参考

有关更多信息，请参阅 [Laravel-Cache](https://laravel.com/docs/8.x/cache)
