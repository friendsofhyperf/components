<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Psr\Container\ContainerInterface;
use Sentry\SentrySdk;
use Throwable;

abstract class CaptureExceptionListener implements ListenerInterface
{
    public function __construct(protected ContainerInterface $container, protected ConfigInterface $config)
    {
    }

    /**
     * @param Throwable $throwable
     */
    protected function captureException($throwable): void
    {
        if (! $throwable instanceof Throwable) {
            return;
        }

        $hub = SentrySdk::getCurrentHub();

        try {
            $hub->captureException($throwable);
        } catch (Throwable $e) {
            $this->container->get(StdoutLoggerInterface::class)->error((string) $e);
        } finally {
            $hub->getClient()?->flush();
        }
    }

    protected function isEnable(string $key): bool
    {
        return (bool) $this->config->get('sentry.enable.' . $key, true);
    }
}
