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
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Throwable;

function report(string|Throwable $exception = 'RuntimeException', ...$parameters)
{
    if (is_string($exception)) {
        $exception = class_exists($exception) ? new $exception(...$parameters) : new RuntimeException($exception, ...$parameters);
    }

    if (! ApplicationContext::hasContainer()) {
        return;
    }

    $container = ApplicationContext::getContainer();

    if (! $container->get(EventDispatcherInterface::class)) {
        return;
    }

    /** @var ServerRequestInterface|null $request */
    $request = Context::get(ServerRequestInterface::class);
    /** @var ResponseInterface|null $response */
    $response = Context::get(ResponseInterface::class);
    $container->get(EventDispatcherInterface::class)->dispatch(new ExceptionDispatched($exception, $request, $response));
}

/**
 * @template T
 *
 * @param T $condition
 * @return T
 */
function report_if($condition, string|Throwable $exception = 'RuntimeException', ...$parameters)
{
    if ($condition) {
        report($exception, ...$parameters);
    }

    return $condition;
}

/**
 * @template T
 *
 * @param T $condition
 * @return T
 */
function report_unless($condition, string|Throwable $exception = 'RuntimeException', ...$parameters)
{
    if (! $condition) {
        report($exception, ...$parameters);
    }

    return $condition;
}
