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

#[Attribute(Attribute::TARGET_METHOD)]
class RateLimit extends AbstractAnnotation
{
    /**
     * @param string|array $key Rate limit key, supports placeholders like {user_id}
     * @param int $maxAttempts Maximum number of attempts allowed
     * @param int $decay Time window in seconds
     * @param Algorithm $algorithm Algorithm to use: fixed_window, sliding_window, token_bucket, leaky_bucket
     * @param string $pool The Redis connection pool to use
     * @param string $response Custom response when rate limit is exceeded
     * @param int $responseCode HTTP response code when rate limit is exceeded
     */
    public function __construct(
        public string|array $key = '',
        public int $maxAttempts = 60,
        public int $decay = 60,
        public Algorithm $algorithm = Algorithm::FIXED_WINDOW,
        public ?string $pool = null,
        public string $response = 'Too Many Attempts, Please try again in %d seconds.',
        public int $responseCode = 429
    ) {
    }
}
