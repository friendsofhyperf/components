<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/sentry.
 *
 * @link     https://github.com/friendsofhyperf/sentry
 * @document https://github.com/friendsofhyperf/sentry/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Sentry;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Sentry\State\HubInterface;
use Throwable;

class SentryExceptionHandler extends ExceptionHandler
{
    public function __construct(protected ContainerInterface $container, protected StdoutLoggerInterface $logger)
    {
    }

    /**
     * Handle the exception, and return the specified result.
     */
    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        defer(function () use ($throwable) {
            try {
                $hub = $this->container->get(HubInterface::class);
                $hub->captureException($throwable);

                $hub->getClient()->flush();
            } catch (Throwable $e) {
                $this->logger->error((string) $e);
            }
        });

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
