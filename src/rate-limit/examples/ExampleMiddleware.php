<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace App\Middleware;

use FriendsOfHyperf\RateLimit\Middleware\RateLimitMiddleware;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Example middleware for API rate limiting.
 */
class ApiRateLimitMiddleware extends RateLimitMiddleware
{
    protected int $maxAttempts = 60;
    
    protected int $decay = 60;
    
    protected string $algorithm = 'sliding_window';
    
    protected string $responseMessage = 'API rate limit exceeded.';

    protected function resolveKey(ServerRequestInterface $request): string
    {
        return 'api:' . $this->getClientIp();
    }
}
