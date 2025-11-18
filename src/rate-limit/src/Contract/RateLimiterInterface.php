<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\RateLimit\Contract;

interface RateLimiterInterface
{
    /**
     * Attempt to acquire the rate limit.
     *
     * @param string $key The unique identifier for the rate limit
     * @param int $maxAttempts Maximum number of attempts allowed
     * @param int $decay Time window in seconds
     * @return bool True if attempt is allowed, false otherwise
     */
    public function attempt(string $key, int $maxAttempts, int $decay): bool;

    /**
     * Check if rate limit has been exceeded.
     *
     * @param string $key The unique identifier for the rate limit
     * @param int $maxAttempts Maximum number of attempts allowed
     * @param int $decay Time window in seconds
     * @return bool True if limit exceeded, false otherwise
     */
    public function tooManyAttempts(string $key, int $maxAttempts, int $decay): bool;

    /**
     * Get the number of attempts for a key.
     *
     * @param string $key The unique identifier for the rate limit
     * @return int Number of attempts
     */
    public function attempts(string $key): int;

    /**
     * Get the number of remaining attempts.
     *
     * @param string $key The unique identifier for the rate limit
     * @param int $maxAttempts Maximum number of attempts allowed
     * @return int Number of remaining attempts
     */
    public function remaining(string $key, int $maxAttempts): int;

    /**
     * Clear all attempts for a key.
     *
     * @param string $key The unique identifier for the rate limit
     */
    public function clear(string $key): void;

    /**
     * Get the number of seconds until the rate limit resets.
     *
     * @param string $key The unique identifier for the rate limit
     * @return int Seconds until reset
     */
    public function availableIn(string $key): int;
}
