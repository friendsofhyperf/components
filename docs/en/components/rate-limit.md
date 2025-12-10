# Rate Limit

Hyperf's rate limiting component, supporting multiple algorithms (fixed window, sliding window, token bucket, leaky bucket).

## Installation

```bash
composer require friendsofhyperf/rate-limit
```

## Requirements

- Hyperf ~3.1.0
- Redis

## Features

- **Multiple Rate Limiting Algorithms**
  - Fixed Window
  - Sliding Window
  - Token Bucket
  - Leaky Bucket
- **Flexible Usage**
  - Annotation-based rate limiting (via aspect)
  - Custom middleware support
- **Smart Multi-Annotation Sorting**
  - Automatically sorts multiple RateLimit annotations by priority
  - Intelligent sorting based on strictness (maxAttempts/decay ratio)
  - Stricter limits checked first for better performance
- **Flexible Key Generation**
  - Default method/class-based keys
  - Support for custom keys and placeholders
  - Support for array keys
  - Support for callable keys
- **Custom Responses**
  - Custom response messages
  - Custom HTTP response codes
- **Multiple Redis Connection Pool Support**

## Usage

### Method 1: Using Annotations

The simplest way is to use the `#[RateLimit]` attribute:

```php
<?php

namespace App\Controller;

use FriendsOfHyperf\RateLimit\Annotation\RateLimit;
use FriendsOfHyperf\RateLimit\Algorithm;

class UserController
{
    /**
     * Basic rate limit: max 60 requests in 60 seconds
     */
    #[RateLimit(maxAttempts: 60, decay: 60)]
    public function index()
    {
        // Your code
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
        // Your code
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
        // Your code
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
        // Your code
    }

    /**
     * Custom response message and status code
     */
    #[RateLimit(
        maxAttempts: 5,
        decay: 60,
        response: 'Too many requests, please try again later.',
        responseCode: 429
    )]
    public function login()
    {
        // Your code
    }

    /**
     * Using specified Redis connection pool
     */
    #[RateLimit(
        maxAttempts: 60,
        decay: 60,
        pool: 'rate_limit'
    )]
    public function heavyOperation()
    {
        // Your code
    }
}
```

### Annotation Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `key` | `string\|array` | `''` | Rate limit key. Supports: 'user:{user_id}', ['user', '{user_id}'], or callable functions |
| `maxAttempts` | `int` | `60` | Maximum number of allowed requests |
| `decay` | `int` | `60` | Time window in seconds |
| `algorithm` | `Algorithm` | `Algorithm::FIXED_WINDOW` | Algorithm: fixed_window, sliding_window, token_bucket, leaky_bucket |
| `pool` | `?string` | `null` | Redis connection pool to use |
| `response` | `string` | `'Too Many Attempts.'` | Custom response when rate limit is exceeded |
| `responseCode` | `int` | `429` | HTTP status code when rate limit is exceeded |

### Using AutoSort for Smart Multi-Rate-Limit Rule Sorting

When a single method requires multiple rate limiting rules (e.g., per-minute and per-hour limits), use the `AutoSort` annotation for automatic sorting by strictness:

```php
<?php

use FriendsOfHyperf\RateLimit\Annotation\RateLimit;
use FriendsOfHyperf\RateLimit\Annotation\AutoSort;

class ApiController
{
    /**
     * Smart sorting of multiple rate limit rules
     * Stricter limits (smaller maxAttempts/decay ratio) are checked first
     */
    #[AutoSort]
    #[RateLimit(maxAttempts: 10, decay: 60)]      // 10 per minute - checked first
    #[RateLimit(maxAttempts: 100, decay: 3600)]    // 100 per hour - checked second
    public function expensiveOperation()
    {
        // Your code
    }

    /**
     * Without AutoSort, checks in declaration order
     */
    #[RateLimit(maxAttempts: 100, decay: 3600)]    // Checked first
    #[RateLimit(maxAttempts: 10, decay: 60)]       // Checked second
    public function anotherOperation()
    {
        // Your code
    }
}
```

**AutoSort Advantages:**

- **Performance**: Stricter limits checked first, avoiding unnecessary checks of looser limits
- **Intelligent**: Automatically calculates priority based on limit strictness (maxAttempts/decay ratio)
- **Optional**: Only takes effect on methods explicitly using `AutoSort`
- **Backward Compatible**: Existing code continues to work without modification

### Key Placeholders

The `key` parameter supports dynamic placeholders that are replaced with method parameters:

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

Then register the middleware in configuration:

```php
// config/autoload/middlewares.php
return [
    'http' => [
        App\Middleware\ApiRateLimitMiddleware::class,
    ],
];
```

## Rate Limiting Algorithms

### Fixed Window (Default)

The simplest algorithm, counting requests within a fixed time window.

```php
#[RateLimit(algorithm: Algorithm::FIXED_WINDOW)]
```

**Advantages**: Simple, memory efficient  
**Disadvantages**: May allow burst requests at window boundaries

### Sliding Window

More accurate than fixed window, distributing requests evenly.

```php
#[RateLimit(algorithm: Algorithm::SLIDING_WINDOW)]
```

**Advantages**: Smooths burst traffic, more accurate  
**Disadvantages**: Slightly more complex

### Token Bucket

Allows burst traffic while maintaining average rate.

```php
#[RateLimit(algorithm: Algorithm::TOKEN_BUCKET)]
```

**Advantages**: Allows burst traffic, flexible  
**Disadvantages**: Requires more configuration

### Leaky Bucket

Processes requests at a constant rate, queuing burst traffic.

```php
#[RateLimit(algorithm: Algorithm::LEAKY_BUCKET)]
```

**Advantages**: Smooth output rate, prevents bursts  
**Disadvantages**: May delay requests

## Custom Rate Limiter

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

The component uses Hyperf's Redis configuration. You can specify the Redis connection pool to use in annotations or middleware:

```php
// Use specific Redis connection pool
#[RateLimit(pool: 'rate_limit')]
```

Ensure Redis connection pools are configured in `config/autoload/redis.php`:

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
    // Login logic
}
```

### Example 2: API Endpoint Rate Limiting

Set different rate limits for different API endpoints:

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

### Example 3: User-Based Rate Limiting

Rate limit by user:

```php
#[RateLimit(
    key: ['user', '{userId}', 'action'],
    maxAttempts: 10,
    decay: 3600 // 1 hour
)]
public function performAction(int $userId)
{
    // Action logic
}
```

### Example 4: IP-Based Rate Limiting

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

### Example 5: Multi-Level Rate Limiting with AutoSort

Efficiently handle multi-level rate limiting for expensive operations using AutoSort:

```php
class ReportController
{
    /**
     * Expensive report generation with multi-level protection
     * AutoSort ensures stricter limits are checked first for better performance
     */
    #[AutoSort]
    #[RateLimit(maxAttempts: 5, decay: 60, response: 'Too many requests. Max 5 per minute')]       // Emergency brake
    #[RateLimit(maxAttempts: 30, decay: 3600, response: 'Hourly limit exceeded. Max 30 per hour')] // Sustained load
    #[RateLimit(maxAttempts: 100, decay: 86400, response: 'Daily limit exceeded. Max 100 per day')] // Daily cap
    public function generateReport($reportType)
    {
        // Expensive report generation logic
    }
}
```