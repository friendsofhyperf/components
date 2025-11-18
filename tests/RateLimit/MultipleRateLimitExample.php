<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace Tests\RateLimit;

use FriendsOfHyperf\RateLimit\Algorithm;
use FriendsOfHyperf\RateLimit\Annotation\RateLimit;

/**
 * Example class demonstrating the new IS_REPEATABLE support for RateLimit annotation.
 * You can now apply multiple RateLimit rules to a single method.
 */
class ApiExampleController
{
    /**
     * Index endpoint with multiple rate limit rules:
     * 1. IP-based limit: 100 requests per minute per IP
     * 2. User-based limit: 1000 requests per hour per user
     * 3. Global endpoint limit: 10 requests per second
     * All rules must pass for the request to be allowed.
     */
    #[RateLimit(
        key: 'ip:{ip}',
        maxAttempts: 100,
        decay: 60,
        algorithm: Algorithm::FIXED_WINDOW,
        response: 'Too many requests from your IP. Please try again in %d seconds.'
    )]
    #[RateLimit(
        key: 'user:{user_id}',
        maxAttempts: 1000,
        decay: 3600,
        algorithm: Algorithm::SLIDING_WINDOW,
        response: 'Hourly limit exceeded for your account. Please try again in %d seconds.'
    )]
    #[RateLimit(
        key: 'api:index',
        maxAttempts: 10,
        decay: 1,
        algorithm: Algorithm::TOKEN_BUCKET,
        response: 'Global rate limit exceeded. Please slow down.'
    )]
    public function index(): array
    {
        return [
            'message' => 'Success',
            'data' => [],
        ];
    }

    /**
     * Login endpoint with strict rate limiting:
     * 1. IP-based limit: 5 attempts per 15 minutes (brute force protection)
     * 2. Global limit: 100 attempts per minute across all IPs
     */
    #[RateLimit(
        key: 'login:ip:{ip}',
        maxAttempts: 5,
        decay: 900,
        response: 'Too many login attempts from your IP. Please wait %d seconds.',
        responseCode: 429
    )]
    #[RateLimit(
        key: 'login:global',
        maxAttempts: 100,
        decay: 60,
        response: 'Login service temporarily unavailable. Please try again in %d seconds.',
        responseCode: 503
    )]
    public function login(string $username, string $password): array
    {
        // Login logic here
        return [
            'message' => 'Login successful',
            'token' => 'example-token',
        ];
    }

    /**
     * Admin endpoint with different rate limits for admin users:
     * 1. Admin user limit: 5000 requests per hour
     * 2. Regular user limit: 100 requests per hour (if accessing as regular user)
     */
    #[RateLimit(
        key: 'admin:data',
        maxAttempts: 5000,
        decay: 3600,
        pool: 'admin_redis',
        response: 'Admin API rate limit exceeded'
    )]
    #[RateLimit(
        key: 'user:basic',
        maxAttempts: 100,
        decay: 3600,
        pool: 'default',
        response: 'Basic user rate limit exceeded'
    )]
    public function adminData(): array
    {
        return [
            'admin_data' => [
                'sensitive' => 'information',
            ],
        ];
    }

    /**
     * Single RateLimit (backward compatibility - still works!)
     * This demonstrates that the original single-annotation usage still works.
     */
    #[RateLimit(
        key: 'simple:endpoint',
        maxAttempts: 60,
        decay: 60,
        response: 'Rate limit exceeded. Please wait %d seconds.'
    )]
    public function simpleEndpoint(): string
    {
        return 'This endpoint has a single rate limit rule.';
    }
}
