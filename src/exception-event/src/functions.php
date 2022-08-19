<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\ExceptionEvent\Event\ExceptionDispatched;
use Hyperf\Context\Context;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

if (class_exists('Hyperf\Utils\ApplicationContext') && ! function_exists('report')) {
    /**
     * @param string|Throwable $exception
     * @param array ...$arguments
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
}

if (! function_exists('report_if')) {
    /**
     * @param mixed $condition
     * @param string|Throwable $exception
     * @param array ...$parameters
     * @throws TypeError
     * @return mixed
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
}

if (! function_exists('report_unless')) {
    /**
     * @param mixed $condition
     * @param string|Throwable $exception
     * @param array ...$parameters
     * @throws TypeError
     * @return mixed
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
}
