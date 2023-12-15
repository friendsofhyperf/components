<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * @deprecated v3.0, will be removed in v3.1, use `FriendsOfHyperf\Sentry\Listener\HttpExceptionListener` instead.
 */
class SentryExceptionHandler extends ExceptionHandler
{
    protected ConfigInterface $config;

    protected StdoutLoggerInterface $logger;

    public function __construct(protected ContainerInterface $container)
    {
        $this->config = $container->get(ConfigInterface::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    /**
     * Handle the exception, and return the specified result.
     */
    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        return $response;
    }

    /**
     * Determine if the current exception handler should handle the exception,.
     *
     * If return true, then this exception handler will handle the exception,
     * If return false, then delegate to next handler
     */
    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}