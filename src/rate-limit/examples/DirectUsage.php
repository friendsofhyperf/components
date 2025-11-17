<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace App\Service;

use FriendsOfHyperf\RateLimit\Exception\RateLimitException;
use FriendsOfHyperf\RateLimit\RateLimiterFactory;

/**
 * Example service showing direct rate limiter usage.
 */
class ExampleService
{
    public function __construct(private RateLimiterFactory $factory)
    {
    }

    public function processUserAction(int $userId): array
    {
        $limiter = $this->factory->make('sliding_window');
        
        $key = 'user:action:' . $userId;
        $maxAttempts = 10;
        $decay = 60;

        if ($limiter->tooManyAttempts($key, $maxAttempts, $decay)) {
            $availableIn = $limiter->availableIn($key);
            throw new RateLimitException(
                "Rate limit exceeded. Try again in {$availableIn} seconds."
            );
        }

        // Process the user action
        return [
            'success' => true,
            'remaining' => $limiter->remaining($key, $maxAttempts),
        ];
    }

    public function checkRateLimit(string $key, int $max, int $decay): array
    {
        $limiter = $this->factory->make('token_bucket');
        
        return [
            'attempts' => $limiter->attempts($key),
            'remaining' => $limiter->remaining($key, $max),
            'available_in' => $limiter->availableIn($key),
        ];
    }
}
