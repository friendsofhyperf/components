# Rate Limit

[![Latest Stable Version](https://poser.pugx.org/friendsofhyperf/rate-limit/v)](https://packagist.org/packages/friendsofhyperf/rate-limit)
[![Total Downloads](https://poser.pugx.org/friendsofhyperf/rate-limit/downloads)](https://packagist.org/packages/friendsofhyperf/rate-limit)
[![License](https://poser.pugx.org/friendsofhyperf/rate-limit/license)](https://packagist.org/packages/friendsofhyperf/rate-limit)

Hyperf 的限流组件，支持多种算法（固定窗口、滑动窗口、令牌桶、漏桶）。

## 安装

```bash
composer require friendsofhyperf/rate-limit
```

## 环境要求

- Hyperf ~3.1.0
- Redis

## 特性

- **多种限流算法**
  - 固定窗口
  - 滑动窗口
  - 令牌桶
  - 漏桶
- **灵活的使用方式**
  - 基于注解的限流（通过切面实现）
  - 自定义中间件支持
- **多注解智能排序**
  - 自动对多个 RateLimit 注解进行优先级排序
  - 根据严格程度智能排序（maxAttempts/decay 比率）
  - 更严格的限制优先检查，提升性能
- **灵活的键生成**
  - 默认基于方法/类的键
  - 支持自定义键和占位符
  - 支持数组键
  - 支持可调用键
- **自定义响应**
  - 自定义响应消息
  - 自定义 HTTP 响应码
- **多 Redis 连接池支持**

## 使用方式

### 方式一：使用注解

最简单的方式是使用 `#[RateLimit]` 属性：

```php
<?php

namespace App\Controller;

use FriendsOfHyperf\RateLimit\Annotation\RateLimit;
use FriendsOfHyperf\RateLimit\Algorithm;

class UserController
{
    /**
     * 基础限流：60 秒内最多 60 次请求
     */
    #[RateLimit(maxAttempts: 60, decay: 60)]
    public function index()
    {
        // 你的代码
    }

    /**
     * 使用滑动窗口算法
     */
    #[RateLimit(
        maxAttempts: 100,
        decay: 60,
        algorithm: Algorithm::SLIDING_WINDOW
    )]
    public function api()
    {
        // 你的代码
    }

    /**
     * 自定义键，支持用户 ID 占位符
     */
    #[RateLimit(
        key: 'user:{userId}:action',
        maxAttempts: 10,
        decay: 3600
    )]
    public function create($userId)
    {
        // 你的代码
    }

    /**
     * 使用数组键
     */
    #[RateLimit(
        key: ['user', '{userId}', 'create'],
        maxAttempts: 5,
        decay: 60
    )]
    public function update($userId)
    {
        // 你的代码
    }

    /**
     * 自定义响应消息和状态码
     */
    #[RateLimit(
        maxAttempts: 5,
        decay: 60,
        response: 'Too many requests, please try again later.',
        responseCode: 429
    )]
    public function login()
    {
        // 你的代码
    }

    /**
     * 使用指定的 Redis 连接池
     */
    #[RateLimit(
        maxAttempts: 60,
        decay: 60,
        pool: 'rate_limit'
    )]
    public function heavyOperation()
    {
        // 你的代码
    }
}
```

#### 注解参数

| 参数 | 类型 | 默认值 | 说明 |
|-----------|------|---------|-------------|
| `key` | `string\|array` | `''` | 限流键。支持：'user:{user_id}', ['user', '{user_id}'], 或可调用函数 |
| `maxAttempts` | `int` | `60` | 允许的最大请求次数 |
| `decay` | `int` | `60` | 时间窗口（秒） |
| `algorithm` | `Algorithm` | `Algorithm::FIXED_WINDOW` | 算法：fixed_window, sliding_window, token_bucket, leaky_bucket |
| `pool` | `?string` | `null` | 使用的 Redis 连接池 |
| `response` | `string` | `'Too Many Attempts.'` | 超出限流时的自定义响应 |
| `responseCode` | `int` | `429` | 超出限流时的 HTTP 状态码 |

#### 使用 AutoSort 实现多限流规则智能排序

当同一个方法需要多个限流规则时（例如每分钟和每小时的限制），可以使用 `AutoSort` 注解自动按严格程度排序：

```php
<?php

use FriendsOfHyperf\RateLimit\Annotation\RateLimit;
use FriendsOfHyperf\RateLimit\Annotation\AutoSort;

class ApiController
{
    /**
     * 多个限流规则智能排序
     * 更严格的限制（maxAttempts/decay 比率更小）优先检查
     */
    #[AutoSort]
    #[RateLimit(maxAttempts: 10, decay: 60)]      // 每分钟 10 次 - 优先检查
    #[RateLimit(maxAttempts: 100, decay: 3600)]    // 每小时 100 次 - 其次检查
    public function expensiveOperation()
    {
        // 你的代码
    }

    /**
     * 不使用 AutoSort 时，按声明顺序检查
     */
    #[RateLimit(maxAttempts: 100, decay: 3600)]    // 优先检查
    #[RateLimit(maxAttempts: 10, decay: 60)]       // 其次检查
    public function anotherOperation()
    {
        // 你的代码
    }
}
```

**AutoSort 的优势：**

- **性能**：严格的限制优先检查，避免不必要的宽松限制检查
- **智能**：自动根据限制严格程度（maxAttempts/decay 比率）计算优先级
- **可选**：仅在显式使用 `AutoSort` 的方法上生效
- **向后兼容**：现有代码无需修改即可继续工作

#### 键占位符

`key` 参数支持动态占位符，会被方法参数替换：

```php
// 命名占位符
#[RateLimit(key: 'user:{userId}:{action}')]
public function action($userId, $action)

// 数组格式（自动用 ':' 连接）
#[RateLimit(key: ['user', '{userId}', '{action}'])]
public function action($userId, $action)
```

### 方式二：使用中间件

对于 HTTP 请求，可以创建继承 `RateLimitMiddleware` 的自定义中间件：

```php
<?php

namespace App\Middleware;

use FriendsOfHyperf\RateLimit\Middleware\RateLimitMiddleware;
use FriendsOfHyperf\RateLimit\Algorithm;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ApiRateLimitMiddleware extends RateLimitMiddleware
{
    // 重写默认属性
    protected int $maxAttempts = 100;
    protected int $decay = 60;
    protected Algorithm $algorithm = Algorithm::SLIDING_WINDOW;
    protected string $responseMessage = 'API rate limit exceeded';
    protected int $responseCode = 429;

    // 或自定义键解析
    protected function resolveKey(ServerRequestInterface $request): string
    {
        return 'api:' . $this->getClientIp();
    }
}
```

然后在配置中注册中间件：

```php
// config/autoload/middlewares.php
return [
    'http' => [
        App\Middleware\ApiRateLimitMiddleware::class,
    ],
];
```

### 限流算法

#### 固定窗口（默认）

最简单的算法，在固定时间窗口内计数请求。

```php
#[RateLimit(algorithm: Algorithm::FIXED_WINDOW)]
```

**优点**：简单，内存高效
**缺点**：可能在窗口边界处允许突发请求

#### 滑动窗口

比固定窗口更准确，均匀分布请求。

```php
#[RateLimit(algorithm: Algorithm::SLIDING_WINDOW)]
```

**优点**：平滑突发流量，更准确
**缺点**：稍微复杂一些

#### 令牌桶

允许突发流量，同时保持平均速率。

```php
#[RateLimit(algorithm: Algorithm::TOKEN_BUCKET)]
```

**优点**：允许突发流量，灵活
**缺点**：需要更多配置

#### 漏桶

以恒定速率处理请求，排队突发流量。

```php
#[RateLimit(algorithm: Algorithm::LEAKY_BUCKET)]
```

**优点**：平滑输出速率，防止突发
**缺点**：可能延迟请求

### 自定义限流器

你可以通过实现 `RateLimiterInterface` 来实现自己的限流器：

```php
<?php

namespace App\RateLimit;

use FriendsOfHyperf\RateLimit\Contract\RateLimiterInterface;

class CustomRateLimiter implements RateLimiterInterface
{
    public function tooManyAttempts(string $key, int $maxAttempts, int $decay): bool
    {
        // 你的实现
    }

    public function availableIn(string $key): int
    {
        // 你的实现
    }

    public function remaining(string $key, int $maxAttempts): int
    {
        // 你的实现
    }
}
```

## 异常处理

当超出限流时，会抛出 `RateLimitException`：

```php
<?php

try {
    $userController->index();
} catch (FriendsOfHyperf\RateLimit\Exception\RateLimitException $e) {
    // 超出限流
    $message = $e->getMessage();  // "Too Many Attempts. Please try again in X seconds."
    $code = $e->getCode();        // 429
}
```

## 配置

组件使用 Hyperf 的 Redis 配置。你可以在注解或中间件中指定使用的 Redis 连接池：

```php
// 使用特定的 Redis 连接池
#[RateLimit(pool: 'rate_limit')]
```

确保在 `config/autoload/redis.php` 中配置 Redis 连接池：

```php
return [
    'default' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'port' => env('REDIS_PORT', 6379),
        'auth' => env('REDIS_AUTH', null),
        'db' => 0,
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 30,
        ],
    ],
    'rate_limit' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'port' => env('REDIS_PORT', 6379),
        'auth' => env('REDIS_AUTH', null),
        'db' => 1,
        'pool' => [
            'min_connections' => 5,
            'max_connections' => 50,
        ],
    ],
];
```

## 示例

### 示例 1：登录限流

限制登录尝试以防止暴力破解：

```php
#[RateLimit(
    key: 'login:{email}',
    maxAttempts: 5,
    decay: 300, // 5 分钟
    response: 'Too many login attempts. Please try again after 5 minutes.',
    responseCode: 429
)]
public function login(string $email, string $password)
{
    // 登录逻辑
}
```

### 示例 2：API 端点限流

为不同的 API 端点设置不同的限流：

```php
class ApiController
{
    // 公共 API：每分钟 100 次请求
    #[RateLimit(maxAttempts: 100, decay: 60)]
    public function public()
    {
        // 公共端点
    }

    // 高级 API：每分钟 1000 次请求
    #[RateLimit(maxAttempts: 1000, decay: 60)]
    public function premium()
    {
        // 高级端点
    }
}
```

### 示例 3：基于用户的限流

按用户限流：

```php
#[RateLimit(
    key: ['user', '{userId}', 'action'],
    maxAttempts: 10,
    decay: 3600 // 1 小时
)]
public function performAction(int $userId)
{
    // 操作逻辑
}
```

### 示例 4：基于 IP 的限流

使用中间件按 IP 地址限流：

```php
class IpRateLimitMiddleware extends RateLimitMiddleware
{
    protected function resolveKey(ServerRequestInterface $request): string
    {
        return 'ip:' . $this->getClientIp();
    }
}
```

### 示例 5：使用 AutoSort 的多级限流

使用 AutoSort 高效处理昂贵操作的多级限流：

```php
class ReportController
{
    /**
     * 昂贵的报告生成，多级保护
     * AutoSort 确保优先检查严格的限制，提升性能
     */
    #[AutoSort]
    #[RateLimit(maxAttempts: 5, decay: 60, response: 'Too many requests. Max 5 per minute')]       // 紧急制动
    #[RateLimit(maxAttempts: 30, decay: 3600, response: 'Hourly limit exceeded. Max 30 per hour')] // 持续负载
    #[RateLimit(maxAttempts: 100, decay: 86400, response: 'Daily limit exceeded. Max 100 per day')] // 每日上限
    public function generateReport($reportType)
    {
        // 昂贵的报告生成逻辑
    }
}
```

## 许可证

[MIT](LICENSE)
