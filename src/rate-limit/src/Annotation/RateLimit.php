<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\RateLimit\Annotation;

use Attribute;
use FriendsOfHyperf\RateLimit\Algorithm;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class RateLimit extends AbstractAnnotation
{
    /**
     * @param string $key Rate limit key, supports placeholders like {user_id}
     * @param int $maxAttempts Maximum number of attempts allowed
     * @param int $decay Time window in seconds
     * @param string $algorithm Algorithm to use: fixed_window, sliding_window, token_bucket, leaky_bucket
     * @param string $response Custom response when rate limit is exceeded
     * @param int $responseCode HTTP response code when rate limit is exceeded
     */
    public function __construct(
        public string $key = '',
        public int $maxAttempts = 60,
        public int $decay = 60,
        public Algorithm $algorithm = Algorithm::FIXED_WINDOW,
        public string $response = 'Too Many Attempts.',
        public int $responseCode = 429
    ) {
    }
}
