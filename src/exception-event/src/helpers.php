<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/exception-event.
 *
 * @link     https://github.com/friendsofhyperf/exception-event
 * @document https://github.com/friendsofhyperf/exception-event/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\ExceptionEvent\Event\ExceptionDispatched;
use Hyperf\Context\Context;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

if (class_exists(\Hyperf\Utils\ApplicationContext::class) && ! function_exists('report')) {
    /**
     * @param array ...$arguments
     */
    function report(string|Throwable $exception = \RuntimeException::class, ...$parameters)
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
}

if (! function_exists('report_if')) {
    /**
     * @param mixed $condition
     * @param array ...$parameters
     * @throws TypeError
     * @return mixed
     */
    function report_if($condition, string|Throwable $exception = \RuntimeException::class, ...$parameters)
    {
        if ($condition) {
            if (is_string($exception)) {
                $exception = class_exists($exception) ? new $exception(...$parameters) : new RuntimeException($exception, ...$parameters);
            }

            report($exception);
        }

        return $condition;
    }
}

if (! function_exists('report_unless')) {
    /**
     * @param mixed $condition
     * @param array ...$parameters
     * @throws TypeError
     * @return mixed
     */
    function report_unless($condition, string|Throwable $exception = \RuntimeException::class, ...$parameters)
    {
        if (! $condition) {
            if (is_string($exception)) {
                $exception = class_exists($exception) ? new $exception(...$parameters) : new RuntimeException($exception, ...$parameters);
            }

            report($exception);
        }

        return $condition;
    }
}
