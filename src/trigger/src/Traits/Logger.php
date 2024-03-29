<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Trigger\Traits;

use FriendsOfHyperf\Trigger\Contact\LoggerInterface as LoggerContact;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Log\LoggerInterface;

/**
 * @property ?string $connection
 * @property ?LoggerInterface $logger
 */
trait Logger
{
    protected function info(string $message, array $context = []): void
    {
        $this->getLogger()?->info($this->formatMessage($message, $context));
    }

    protected function debug(string $message, array $context = []): void
    {
        $this->getLogger()?->debug($this->formatMessage($message, $context));
    }

    protected function warning(string $message, array $context = []): void
    {
        $this->getLogger()?->warning($this->formatMessage($message, $context));
    }

    protected function error(string $message, array $context = []): void
    {
        $this->getLogger()?->error($this->formatMessage($message, $context));
    }

    protected function formatMessage(string $message, array $context = []): string
    {
        return sprintf(
            '[trigger%s] %s %s',
            isset($this->connection) ? ".{$this->connection}" : 'default',
            $message,
            $context ? json_encode($context, JSON_UNESCAPED_UNICODE) : ''
        );
    }

    protected function getLogger(): ?LoggerInterface
    {
        if (isset($this->logger) && $this->logger instanceof LoggerInterface) {
            return $this->logger;
        }

        $container = ApplicationContext::getContainer();

        return match (true) {
            $container->has(LoggerContact::class) => $container->get(LoggerContact::class),
            $container->has(StdoutLoggerInterface::class) => $container->get(StdoutLoggerInterface::class),
            default => null,
        };
    }
}
