<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace App\Controller;

use FriendsOfHyperf\RateLimit\Annotation\RateLimit;

/**
 * Example controller demonstrating various rate limiting strategies.
 */
class ExampleController
{
    /**
     * Global API rate limit using IP address.
     */
    #[RateLimit(
        key: "api:global:{ip}",
        maxAttempts: 60,
        decay: 60,
        algorithm: "sliding_window"
    )]
    public function index()
    {
        return ['message' => 'API index'];
    }

    /**
     * Login endpoint with strict rate limiting.
     */
    #[RateLimit(
        key: "login:{ip}",
        maxAttempts: 5,
        decay: 300,
        algorithm: "fixed_window",
        response: "Too many login attempts. Please try again later.",
        responseCode: 429
    )]
    public function login()
    {
        return ['message' => 'Login'];
    }

    /**
     * User-specific rate limit using user ID.
     */
    #[RateLimit(
        key: "user:profile:{user_id}",
        maxAttempts: 30,
        decay: 60,
        algorithm: "token_bucket"
    )]
    public function profile(int $userId)
    {
        return ['user_id' => $userId, 'message' => 'User profile'];
    }

    /**
     * Heavy operation with leaky bucket to smooth out requests.
     */
    #[RateLimit(
        key: "heavy:operation:{ip}",
        maxAttempts: 10,
        decay: 60,
        algorithm: "leaky_bucket"
    )]
    public function heavyOperation()
    {
        return ['message' => 'Heavy operation completed'];
    }
}
