# Rate Limit

[![Latest Version on Packagist](https://img.shields.io/packagist/v/friendsofhyperf/rate-limit.svg?style=flat-square)](https://packagist.org/packages/friendsofhyperf/rate-limit)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/rate-limit.svg?style=flat-square)](https://packagist.org/packages/friendsofhyperf/rate-limit)

Rate limiting component for Hyperf with support for multiple algorithms.

## Features

- **Multiple Algorithms**: Fixed Window, Sliding Window, Token Bucket, Leaky Bucket
- **Annotation Support**: Easy declarative rate limiting with `#[RateLimit]` annotation
- **AOP Integration**: Automatic rate limiting via aspect-oriented programming
- **Middleware**: HTTP middleware for request rate limiting
- **Redis + Lua**: Atomic operations using Redis with Lua scripts
- **Dynamic Configuration**: Support for config center integration
- **Flexible Key Resolution**: Support for placeholders like `{ip}`, `{user_id}`, etc.

## Installation

```bash
composer require friendsofhyperf/rate-limit
```

## Publish Configuration

```bash
php bin/hyperf.php vendor:publish friendsofhyperf/rate-limit
```

This will create a `config/autoload/rate_limit.php` configuration file.

## Usage

### Using Annotations

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
        // Login logic
    }
}
```

### Using Middleware

Create a custom middleware that extends `RateLimitMiddleware`:

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

Then register it in your middleware configuration.

### Using Directly in Code

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
            throw new \Exception('Rate limit exceeded');
        }

        // Your logic here
    }
}
```

## Algorithms

### Fixed Window

Simple counter that resets at fixed intervals. Fast but can allow bursts at window boundaries.

```php
use FriendsOfHyperf\RateLimit\Algorithm;

#[RateLimit(algorithm: Algorithm::FIXED_WINDOW, maxAttempts: 100, decay: 60)]
```

### Sliding Window

More accurate than fixed window, uses sorted sets to track requests with timestamps.

```php
use FriendsOfHyperf\RateLimit\Algorithm;

#[RateLimit(algorithm: Algorithm::SLIDING_WINDOW, maxAttempts: 100, decay: 60)]
```

### Token Bucket

Allows bursts up to bucket capacity, tokens are added at a constant rate.

```php
use FriendsOfHyperf\RateLimit\Algorithm;

#[RateLimit(algorithm: Algorithm::TOKEN_BUCKET, maxAttempts: 100, decay: 60)]
```

### Leaky Bucket

Smooths out bursts, processes requests at a constant rate regardless of arrival pattern.

```php
use FriendsOfHyperf\RateLimit\Algorithm;

#[RateLimit(algorithm: Algorithm::LEAKY_BUCKET, maxAttempts: 100, decay: 60)]
```

## Configuration

The configuration file supports:

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

## Key Placeholders

The annotation supports dynamic placeholders in the key:

- `{ip}` - Client IP address
- `{user_id}` - User ID from request attributes
- Any method argument name

Example:

```php
#[RateLimit(key: "api:{ip}:user:{user_id}", maxAttempts: 60, decay: 60)]
public function profile($userId)
{
    // ...
}
```

## License

MIT
