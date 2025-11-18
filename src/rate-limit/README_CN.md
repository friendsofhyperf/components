# 限流组件

[![Latest Version on Packagist](https://img.shields.io/packagist/v/friendsofhyperf/rate-limit.svg?style=flat-square)](https://packagist.org/packages/friendsofhyperf/rate-limit)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/rate-limit.svg?style=flat-square)](https://packagist.org/packages/friendsofhyperf/rate-limit)

为 Hyperf 提供的限流组件，支持多种限流算法。

## 特性

- **多种算法**：固定窗口、滑动窗口、令牌桶、漏桶
- **注解支持**：使用 `#[RateLimit]` 注解轻松实现声明式限流
- **AOP 集成**：通过面向切面编程自动限流
- **中间件**：提供 HTTP 请求限流中间件
- **Redis + Lua**：使用 Redis 和 Lua 脚本实现原子化操作
- **动态配置**：支持配置中心集成
- **灵活的键解析**：支持 `{ip}`、`{user_id}` 等占位符

## 安装

```bash
composer require friendsofhyperf/rate-limit
```

## 发布配置

```bash
php bin/hyperf.php vendor:publish friendsofhyperf/rate-limit
```

这将创建 `config/autoload/rate_limit.php` 配置文件。

## 使用

### 使用注解

```php
use FriendsOfHyperf\RateLimit\Algorithm;
use FriendsOfHyperf\RateLimit\Annotation\RateLimit;

class UserController
{
    #[RateLimit(key: "api:{ip}", maxAttempts: 60, decay: 60, algorithm: Algorithm::SLIDING_WINDOW)]
    public function index()
    {
        return ['message' => 'Hello World'];
    }

    #[RateLimit(key: "login:{ip}", maxAttempts: 5, decay: 60, algorithm: Algorithm::FIXED_WINDOW)]
    public function login()
    {
        // 登录逻辑
    }
}
```

### 使用中间件

创建一个继承 `RateLimitMiddleware` 的自定义中间件：

```php
namespace App\Middleware;

use FriendsOfHyperf\RateLimit\Algorithm;
use FriendsOfHyperf\RateLimit\Middleware\RateLimitMiddleware;
use Psr\Http\Message\ServerRequestInterface;

class ApiRateLimitMiddleware extends RateLimitMiddleware
{
    protected int $maxAttempts = 60;
    protected int $decay = 60;
    protected Algorithm $algorithm = Algorithm::SLIDING_WINDOW;

    protected function resolveKey(ServerRequestInterface $request): string
    {
        return 'api:' . $this->getClientIp();
    }
}
```

然后在中间件配置中注册它。

### 在代码中直接使用

```php
use FriendsOfHyperf\RateLimit\Algorithm;
use FriendsOfHyperf\RateLimit\RateLimiterFactory;

class YourService
{
    public function __construct(private RateLimiterFactory $factory)
    {
    }

    public function someMethod()
    {
        $limiter = $this->factory->make(Algorithm::SLIDING_WINDOW);

        $key = 'operation:user:123';
        $maxAttempts = 10;
        $decay = 60;

        if ($limiter->tooManyAttempts($key, $maxAttempts, $decay)) {
            throw new \Exception('超出限流次数');
        }

        // 你的业务逻辑
    }
}
```

## 算法说明

### 固定窗口（Fixed Window）

简单的计数器，在固定时间间隔重置。速度快但可能在窗口边界出现突发流量。

```php
use FriendsOfHyperf\RateLimit\Algorithm;

#[RateLimit(algorithm: Algorithm::FIXED_WINDOW, maxAttempts: 100, decay: 60)]
```

### 滑动窗口（Sliding Window）

比固定窗口更精确，使用有序集合跟踪带时间戳的请求。

```php
use FriendsOfHyperf\RateLimit\Algorithm;

#[RateLimit(algorithm: Algorithm::SLIDING_WINDOW, maxAttempts: 100, decay: 60)]
```

### 令牌桶（Token Bucket）

允许突发流量达到桶容量，令牌以恒定速率添加。

```php
use FriendsOfHyperf\RateLimit\Algorithm;

#[RateLimit(algorithm: Algorithm::TOKEN_BUCKET, maxAttempts: 100, decay: 60)]
```

### 漏桶（Leaky Bucket）

平滑突发流量，无论到达模式如何，都以恒定速率处理请求。

```php
use FriendsOfHyperf\RateLimit\Algorithm;

#[RateLimit(algorithm: Algorithm::LEAKY_BUCKET, maxAttempts: 100, decay: 60)]
```

## 配置

配置文件支持以下选项：

```php
return [
    'default' => 'fixed_window',
    'connection' => 'default',
    'prefix' => 'rate_limit',
    
    'defaults' => [
        'max_attempts' => 60,
        'decay' => 60,
    ],
    
    'limiters' => [
        'api' => [
            'max_attempts' => 60,
            'decay' => 60,
            'algorithm' => 'sliding_window',
        ],
        'login' => [
            'max_attempts' => 5,
            'decay' => 60,
            'algorithm' => 'fixed_window',
        ],
    ],
];
```

## 键占位符

注解支持在键中使用动态占位符：

- `{ip}` - 客户端 IP 地址
- `{user_id}` - 从请求属性获取的用户 ID
- 任何方法参数名称

示例：

```php
#[RateLimit(key: "api:{ip}:user:{user_id}", maxAttempts: 60, decay: 60)]
public function profile($userId)
{
    // ...
}
```

## 许可证

MIT
