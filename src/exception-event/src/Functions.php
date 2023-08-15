<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ExceptionEvent;

use FriendsOfHyperf\ExceptionEvent\Event\ExceptionDispatched;
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

/**
 * @param string|Throwable $exception
 */
function report($exception = 'RuntimeException', ...$parameters)
{
    if (is_string($exception)) {
        $exception = class_exists($exception) ? new $exception(...$parameters) : new RuntimeException($exception, ...$parameters);
    }

    if (ApplicationContext::hasContainer()) {
        /** @var ServerRequestInterface $request */
        $request = Context::get(ServerRequestInterface::class);
        /** @var ResponseInterface $response */
        $response = Context::get(ResponseInterface::class);
        /** @var ContainerInterface $container */
        $container = ApplicationContext::getContainer();
        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $container->get(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(new ExceptionDispatched($exception, $request, $response));
    }
}

/**
 * @template T
 *
 * @param T $condition
 * @param string|Throwable $exception
 * @param array ...$parameters
 * @return T
 * @throws TypeError
 */
function report_if($condition, $exception = 'RuntimeException', ...$parameters)
{
    if ($condition) {
        if (is_string($exception)) {
            $exception = class_exists($exception) ? new $exception(...$parameters) : new RuntimeException($exception, ...$parameters);
        }

        report($exception);
    }

    return $condition;
}

/**
 * @template T
 *
 * @param T $condition
 * @param string|Throwable $exception
 * @param array ...$parameters
 * @return T
 * @throws TypeError
 */
function report_unless($condition, $exception = 'RuntimeException', ...$parameters)
{
    if (! $condition) {
        if (is_string($exception)) {
            $exception = class_exists($exception) ? new $exception(...$parameters) : new RuntimeException($exception, ...$parameters);
        }

        report($exception);
    }

    return $condition;
}
