<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ExceptionEvent{
    use FriendsOfHyperf\ExceptionEvent\Event\ExceptionDispatched;
    use Hyperf\Context\ApplicationContext;
    use Hyperf\Context\Context;
    use Psr\Container\ContainerInterface;
    use Psr\EventDispatcher\EventDispatcherInterface;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use RuntimeException;

    /**
     * @param string|\Throwable $exception
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
     * @param string|\Throwable $exception
     * @param array ...$parameters
     * @return T
     * @throws \TypeError
     */
    function report_if($condition, $exception = 'RuntimeException', ...$parameters)
    {
        if ($condition) {
            if (is_string($exception)) {
                $exception = class_exists($exception) ? new $exception(...$parameters) : new RuntimeException($exception, ...$parameters); /* @phpstan-ignore-line */
            }

            report($exception);
        }

        return $condition;
    }

    /**
     * @template T
     *
     * @param T $condition
     * @param string|\Throwable $exception
     * @param array ...$parameters
     * @return T
     * @throws \TypeError
     */
    function report_unless($condition, $exception = 'RuntimeException', ...$parameters)
    {
        if (! $condition) {
            if (is_string($exception)) {
                $exception = class_exists($exception) ? new $exception(...$parameters) : new RuntimeException($exception, ...$parameters); /* @phpstan-ignore-line */
            }

            report($exception);
        }

        return $condition;
    }
}

namespace {
    if (! function_exists('report')) {
        /**
         * @param string|\Throwable $exception
         * @deprecated since 3.1, use `\FriendsOfHyperf\ExceptionEvent\report` instead.
         */
        function report($exception = 'RuntimeException', ...$parameters)
        {
            return \FriendsOfHyperf\ExceptionEvent\report($exception, ...$parameters);
        }
    }

    if (! function_exists('report_if')) {
        /**
         * @template T
         *
         * @param T $condition
         * @param string|\Throwable $exception
         * @param array ...$parameters
         * @return T
         * @throws TypeError
         * @deprecated since 3.1, use `\FriendsOfHyperf\ExceptionEvent\report_if` instead.
         */
        function report_if($condition, $exception = 'RuntimeException', ...$parameters)
        {
            return \FriendsOfHyperf\ExceptionEvent\report_if($condition, $exception, ...$parameters);
        }
    }

    if (! function_exists('report_unless')) {
        /**
         * @template T
         *
         * @param T $condition
         * @param string|\Throwable $exception
         * @param array ...$parameters
         * @return T
         * @throws TypeError
         * @deprecated since 3.1, use `\FriendsOfHyperf\ExceptionEvent\report_unless` instead.
         */
        function report_unless($condition, $exception = 'RuntimeException', ...$parameters)
        {
            return \FriendsOfHyperf\ExceptionEvent\report_unless($condition, $exception, ...$parameters);
        }
    }
}
