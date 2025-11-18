<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use function Hyperf\Support\env;

return [
    /*
     * Default rate limiter algorithm
     * Supported: "fixed_window", "sliding_window", "token_bucket", "leaky_bucket"
     */
    'default' => env('RATE_LIMIT_ALGORITHM', 'fixed_window'),

    /*
     * Redis connection name
     * Uses the default Redis connection if not specified
     */
    'connection' => env('RATE_LIMIT_REDIS_CONNECTION', 'default'),

    /*
     * Prefix for rate limit keys in Redis
     */
    'prefix' => env('RATE_LIMIT_PREFIX', 'rate_limit'),

    /*
     * Default rate limit settings
     */
    'defaults' => [
        'max_attempts' => 60,
        'decay' => 60, // seconds
    ],

    /*
     * Named rate limiters
     * You can define custom rate limiters here
     */
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
        'global' => [
            'max_attempts' => 1000,
            'decay' => 60,
            'algorithm' => 'token_bucket',
        ],
    ],
];
