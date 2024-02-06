<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Confd\Traits;

use FriendsOfHyperf\Confd\Contract\LoggerInterface as LoggerContract;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Log\LoggerInterface;

trait Logger
{
    protected ?LoggerInterface $logger = null;

    protected function resolveLogger()
    {
        $this->logger = $this->resolveLoggerInstance();
    }

    protected function resolveLoggerInstance(): ?LoggerInterface
    {
        if (! ApplicationContext::hasContainer()) {
            return null;
        }

        $container = ApplicationContext::getContainer();

        return match (true) {
            $container->has(LoggerContract::class) => $container->get(LoggerContract::class),
            $container->has(StdoutLoggerInterface::class) => $container->get(StdoutLoggerInterface::class),
            default => null,
        };
    }
}
