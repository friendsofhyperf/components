<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\RateLimit\Exception;

use RuntimeException;
use Throwable;

class RateLimitException extends RuntimeException
{
    public function __construct(
        string $message = '',
        int $code = 0,
        public ?int $remaining = null,
        public ?int $availableIn = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
