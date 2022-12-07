<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
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
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
                $this->container->get(StdoutLoggerInterface::class)->error((string) $e);
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
