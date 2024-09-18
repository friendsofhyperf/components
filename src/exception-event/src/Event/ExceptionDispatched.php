<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ExceptionEvent\Event;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class ExceptionDispatched
{
    public function __construct(
        public Throwable $throwable,
        public ?ServerRequestInterface $request = null,
        public ?ResponseInterface $response = null
    ) {
    }

    /**
     * @deprecated since v3.1, use property instead, will be removed in v3.2
     */
    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }
}
