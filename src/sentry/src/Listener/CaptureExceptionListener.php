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

use FriendsOfHyperf\Sentry\Integration;
use FriendsOfHyperf\Sentry\Switcher;
use Hyperf\Context\Context;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Psr\Container\ContainerInterface;
use Sentry\SentrySdk;
use Throwable;

abstract class CaptureExceptionListener implements ListenerInterface
{
    public const SETUP = 'sentry.context.setup';

    public function __construct(protected ContainerInterface $container, protected Switcher $switcher)
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

    protected function setupSentrySdk(): void
    {
        if (Context::has(static::SETUP)) {
            if ($this->container->has(StdoutLoggerInterface::class)) {
                $this->container->get(StdoutLoggerInterface::class)->warning('SentrySdk has been setup.');
            }

            return;
        }

        SentrySdk::init();
        Context::set(static::SETUP, true);
    }

    protected function flushEvents(): void
    {
        Integration::flushEvents();
    }
}
