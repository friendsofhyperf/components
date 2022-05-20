<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/exception-event.
 *
 * @link     https://github.com/friendsofhyperf/exception-event
 * @document https://github.com/friendsofhyperf/exception-event/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ExceptionEvent\Event;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class ExceptionDispatched
{
    public function __construct(public Throwable $throwable, public ?ServerRequestInterface $request = null, public ?ResponseInterface $response = null)
    {
    }

    /**
     * @return Throwable
     */
    public function getThrowable()
    {
        return $this->throwable;
    }
}
