# Rate Limit

[![Latest Stable Version](https://poser.pugx.org/friendsofhyperf/rate-limit/v)](https://packagist.org/packages/friendsofhyperf/rate-limit)
[![Total Downloads](https://poser.pugx.org/friendsofhyperf/rate-limit/downloads)](https://packagist.org/packages/friendsofhyperf/rate-limit)
[![License](https://poser.pugx.org/friendsofhyperf/rate-limit/license)](https://packagist.org/packages/friendsofhyperf/rate-limit)

Rate limiting component for Hyperf with support for multiple algorithms (Fixed Window, Sliding Window, Token Bucket, Leaky Bucket).

## Installation

```bash
composer require friendsofhyperf/rate-limit
```

## Requirements

- Hyperf ~3.2.0
- Redis

## Features

- **Multiple Rate Limiting Algorithms**
  - Fixed Window
  - Sliding Window
  - Token Bucket
  - Leaky Bucket
- **Flexible Usage**
  - Annotation-based rate limiting via Aspect
  - Custom middleware support
- **Smart Order for Multiple Annotations**
  - Automatic prioritization of multiple RateLimit annotations
  - Intelligent ordering by strictness (maxAttempts/decay ratio)
  - More restrictive limits evaluated first for better performance
- **Flexible Key Generation**
  - Default method/class-based keys
  - Custom key with placeholders support
  - Array keys support
  - Callable keys support
- **Customizable Responses**
  - Custom response message
  - Custom HTTP response code
- **Multi Redis Pool Support**

## Usage

### Method 1: Using Annotation

The easiest way to add rate limiting is using the `#[RateLimit]` attribute:

```php
<?php

namespace App\Controller;

use FriendsOfHyperf\RateLimit\Annotation\RateLimit;
use FriendsOfHyperf\RateLimit\Algorithm;

class UserController
{
    /**
     * Basic rate limiting: 60 attempts per 60 seconds
     */
    #[RateLimit(maxAttempts: 60, decay: 60)]
    public function index()
    {
        // Your code here
    }

    /**
     * Using sliding window algorithm
     */
    #[RateLimit(
        maxAttempts: 100,
        decay: 60,
        algorithm: Algorithm::SLIDING_WINDOW
    )]
    public function api()
    {
        // Your code here
    }

    /**
     * Custom key with user ID placeholder
     */
    #[RateLimit(
        key: 'user:{userId}:action',
        maxAttempts: 10,
        decay: 3600
    )]
    public function create($userId)
    {
        // Your code here
    }

    /**
     * Using array key
     */
    #[RateLimit(
        key: ['user', '{userId}', 'create'],
        maxAttempts: 5,
        decay: 60
    )]
    public function update($userId)
    {
        // Your code here
    }

    /**
     * Custom response message and code
     */
    #[RateLimit(
        maxAttempts: 5,
        decay: 60,
        response: 'Too many requests, please try again later.',
        responseCode: 429
    )]
    public function login()
    {
        // Your code here
    }

    /**
     * Using specific Redis pool
     */
    #[RateLimit(
        maxAttempts: 60,
        decay: 60,
        pool: 'rate_limit'
    )]
    public function heavyOperation()
    {
        // Your code here
    }
}
```

#### Annotation Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `key` | `string\|array` | `''` | Rate limit key. Supports: 'user:{user_id}', ['user', '{user_id}'], or callable |
| `maxAttempts` | `int` | `60` | Maximum number of attempts allowed |
| `decay` | `int` | `60` | Time window in seconds |
| `algorithm` | `Algorithm` | `Algorithm::FIXED_WINDOW` | Algorithm to use: fixed_window, sliding_window, token_bucket, leaky_bucket |
| `pool` | `?string` | `null` | The Redis connection pool to use |
| `response` | `string` | `'Too Many Attempts.'` | Custom response when rate limit is exceeded |
| `responseCode` | `int` | `429` | HTTP response code when rate limit is exceeded |

#### Multiple RateLimits with AutoSort

When you need multiple rate limits on the same method (e.g., per-minute and per-hour limits), you can use the `AutoSort` annotation to automatically prioritize them:

```php
<?php

use FriendsOfHyperf\RateLimit\Annotation\RateLimit;
use FriendsOfHyperf\RateLimit\Annotation\AutoSort;

class ApiController
{
    /**
     * Multiple rate limits with smart prioritization
     * Stricter limits (smaller maxAttempts/decay ratio) are evaluated first
     */
    #[AutoSort]
    #[RateLimit(maxAttempts: 10, decay: 60)]      // 10 requests/minute - evaluated first
    #[RateLimit(maxAttempts: 100, decay: 3600)]    // 100 requests/hour - evaluated second
    public function expensiveOperation()
    {
        // Your code here
    }

    /**
     * Without AutoSort, rate limits are evaluated in declaration order
     */
    #[RateLimit(maxAttempts: 100, decay: 3600)]    // Evaluated first
    #[RateLimit(maxAttempts: 10, decay: 60)]       // Evaluated second
    public function anotherOperation()
    {
        // Your code here
    }
}
```

**Benefits of AutoSort:**

- **Performance**: Stricter limits are checked first, avoiding unnecessary checks of more lenient limits
- **Intelligence**: Automatically calculates priority based on limit strictness (maxAttempts/decay ratio)
- **Opt-in**: Only affects methods where `AutoSort` is explicitly used
- **Backward Compatible**: Existing code continues to work without changes

#### Key Placeholders

The `key` parameter supports dynamic placeholders that will be replaced with method arguments:

```php
// Named placeholders
#[RateLimit(key: 'user:{userId}:{action}')]
public function action($userId, $action)

// Array format (automatically joined with ':')
#[RateLimit(key: ['user', '{userId}', '{action}'])]
public function action($userId, $action)
```

### Method 2: Using Middleware

For HTTP requests, you can create custom middleware extending `RateLimitMiddleware`:

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
    // Override default properties
    protected int $maxAttempts = 100;
    protected int $decay = 60;
    protected Algorithm $algorithm = Algorithm::SLIDING_WINDOW;
    protected string $responseMessage = 'API rate limit exceeded';
    protected int $responseCode = 429;

    // Or customize key resolution
    protected function resolveKey(ServerRequestInterface $request): string
    {
        return 'api:' . $this->getClientIp();
    }
}
```

Then register the middleware in your config:

```php
// config/autoload/middlewares.php
return [
    'http' => [
        App\Middleware\ApiRateLimitMiddleware::class,
    ],
];
```

### Rate Limiting Algorithms

#### Fixed Window (默认)

Simplest algorithm, counts requests in fixed time windows.

```php
#[RateLimit(algorithm: Algorithm::FIXED_WINDOW)]
```

**Pros**: Simple, memory efficient
**Cons**: Can allow burst requests at window boundaries

#### Sliding Window

More accurate than fixed window, spreads requests evenly.

```php
#[RateLimit(algorithm: Algorithm::SLIDING_WINDOW)]
```

**Pros**: Smooths out bursts, more accurate
**Cons**: Slightly more complex

#### Token Bucket

Allows burst traffic while maintaining average rate.

```php
#[RateLimit(algorithm: Algorithm::TOKEN_BUCKET)]
```

**Pros**: Allows burst traffic, flexible
**Cons**: Requires more configuration

#### Leaky Bucket

Processes requests at constant rate, queues bursts.

```php
#[RateLimit(algorithm: Algorithm::LEAKY_BUCKET)]
```

**Pros**: Smooth output rate, prevents bursts
**Cons**: Can delay requests

### Custom Rate Limiter

You can implement your own rate limiter by implementing `RateLimiterInterface`:

```php
<?php

namespace App\RateLimit;

use FriendsOfHyperf\RateLimit\Contract\RateLimiterInterface;

class CustomRateLimiter implements RateLimiterInterface
{
    public function tooManyAttempts(string $key, int $maxAttempts, int $decay): bool
    {
        // Your implementation
    }

    public function availableIn(string $key): int
    {
        // Your implementation
    }

    public function remaining(string $key, int $maxAttempts): int
    {
        // Your implementation
    }
}
```

## Exception Handling

When rate limit is exceeded, a `RateLimitException` is thrown:

```php
<?php

try {
    $userController->index();
} catch (FriendsOfHyperf\RateLimit\Exception\RateLimitException $e) {
    // Rate limit exceeded
    $message = $e->getMessage();  // "Too Many Attempts. Please try again in X seconds."
    $code = $e->getCode();        // 429
}
```

## Configuration

The component uses Hyperf's Redis configuration. You can specify which Redis pool to use in the annotation or middleware:

```php
// Using specific Redis pool
#[RateLimit(pool: 'rate_limit')]
```

Make sure to configure your Redis pool in `config/autoload/redis.php`:

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

## Examples

### Example 1: Login Rate Limiting

Limit login attempts to prevent brute force attacks:

```php
#[RateLimit(
    key: 'login:{email}',
    maxAttempts: 5,
    decay: 300, // 5 minutes
    response: 'Too many login attempts. Please try again after 5 minutes.',
    responseCode: 429
)]
public function login(string $email, string $password)
{
    // Login logic here
}
```

### Example 2: API Endpoint Rate Limit

Different rate limits for different API endpoints:

```php
class ApiController
{
    // Public API: 100 requests per minute
    #[RateLimit(maxAttempts: 100, decay: 60)]
    public function public()
    {
        // Public endpoint
    }

    // Premium API: 1000 requests per minute
    #[RateLimit(maxAttempts: 1000, decay: 60)]
    public function premium()
    {
        // Premium endpoint
    }
}
```

### Example 3: User-based Rate Limiting

Rate limit per user:

```php
#[RateLimit(
    key: ['user', '{userId}', 'action'],
    maxAttempts: 10,
    decay: 3600 // 1 hour
)]
public function performAction(int $userId)
{
    // Action logic here
}
```

### Example 4: IP-based Rate Limiting

Rate limit by IP address using middleware:

```php
class IpRateLimitMiddleware extends RateLimitMiddleware
{
    protected function resolveKey(ServerRequestInterface $request): string
    {
        return 'ip:' . $this->getClientIp();
    }
}
```

### Example 5: Multiple Rate Limits with Smart Order

Use AutoSort to efficiently handle multiple rate limits on expensive operations:

```php
class ReportController
{
    /**
     * Expensive report generation with multiple protection levels
     * AutoSort ensures stricter limits are checked first for better performance
     */
    #[AutoSort]
    #[RateLimit(maxAttempts: 5, decay: 60, response: 'Too many requests. Max 5 per minute')]       // Emergency brake
    #[RateLimit(maxAttempts: 30, decay: 3600, response: 'Hourly limit exceeded. Max 30 per hour')] // Sustained load
    #[RateLimit(maxAttempts: 100, decay: 86400, response: 'Daily limit exceeded. Max 100 per day')] // Daily cap
    public function generateReport($reportType)
    {
        // Expensive report generation logic here
    }
}
```

## License

[MIT](LICENSE)
