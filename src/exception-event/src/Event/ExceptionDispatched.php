<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ExceptionEvent\Event;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class ExceptionDispatched
{
    /**
     * @var Throwable
     */
    public $throwable;

    /**
     * @var null|ServerRequestInterface
     */
    public $request;

    /**
     * @var null|ResponseInterface
     */
    public $response;

    public function __construct(Throwable $throwable, ?ServerRequestInterface $request = null, ?ResponseInterface $response = null)
    {
        $this->throwable = $throwable;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @return Throwable
     */
    public function getThrowable()
    {
        return $this->throwable;
    }
}
