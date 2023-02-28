<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Sentry;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Sentry\SentrySdk;
use Throwable;

class SentryExceptionHandler extends ExceptionHandler
{
    protected ConfigInterface $config;

    public function __construct(protected ContainerInterface $container)
    {
        $this->config = $container->get(ConfigInterface::class);
    }

    /**
     * Handle the exception, and return the specified result.
     */
    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        defer(function () use ($throwable) {
            try {
                $hub = SentrySdk::getCurrentHub();

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
        if (method_exists($throwable, 'shouldntReportSentry') && $throwable->shouldntReportSentry()) {
            return false;
        }

        $dontReport = $this->config->get('sentry.dont_report', []);

        foreach ($dontReport as $type) {
            if ($throwable instanceof $type) {
                return false;
            }
        }

        return true;
    }
}
