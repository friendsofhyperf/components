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

use Sentry\Breadcrumb;
use Sentry\Event;
use Sentry\Integration\IntegrationInterface;
use Sentry\SentrySdk;
use Sentry\State\Scope;
use Sentry\Tracing\Span;

use function Sentry\addBreadcrumb;
use function Sentry\configureScope;

use const SWOOLE_VERSION;

class Integration implements IntegrationInterface
{
    private static ?string $transaction = null;

    public function setupOnce(): void
    {
        Scope::addGlobalEventProcessor(function (Event $event): Event {
            $self = SentrySdk::getCurrentHub()->getIntegration(self::class);

            if (! $self instanceof self) {
                return $event;
            }

            if (defined('\SWOOLE_VERSION')) {
                $event->setContext('swoole', [
                    'version' => SWOOLE_VERSION,
                ]);
            }

            if (defined('\Swow\Extension::VERSION')) {
                $event->setContext('swow', [
                    /* @phpstan-ignore-next-line */
                    'version' => \Swow\Extension::VERSION,
                ]);
            }

            if (empty($event->getTransaction())) {
                $event->setTransaction($self->getTransaction());
            }

            return $event;
        });
    }

    /**
     * Adds a breadcrumb.
     */
    public static function addBreadcrumb(Breadcrumb $breadcrumb): void
    {
        $self = SentrySdk::getCurrentHub()->getIntegration(self::class);

        if (! $self instanceof self) {
            return;
        }

        addBreadcrumb($breadcrumb);
    }

    /**
     * Configures the scope.
     */
    public static function configureScope(callable $callback): void
    {
        $self = SentrySdk::getCurrentHub()->getIntegration(self::class);

        if (! $self instanceof self) {
            return;
        }

        configureScope($callback);
    }

    public static function getTransaction(): ?string
    {
        return self::$transaction;
    }

    public static function setTransaction(?string $transaction): void
    {
        self::$transaction = $transaction;
    }

    /**
     * Block until all async events are processed for the HTTP transport.
     *
     * @internal this is not part of the public API and is here temporarily until
     *  the underlying issue can be resolved, this method will be removed
     */
    public static function flushEvents(): void
    {
        $client = SentrySdk::getCurrentHub()->getClient();

        if ($client !== null) {
            $client->flush();
        }
    }

    /**
     * Retrieve the meta tags with tracing information to link this request to front-end requests.
     */
    public static function sentryTracingMeta(): string
    {
        $span = self::currentTracingSpan();

        if ($span === null) {
            return '';
        }

        return sprintf('<meta name="sentry-trace" content="%s"/>', $span->toTraceparent());
    }

    /**
     * Get the current active tracing span from the scope.
     *
     * @internal this is used internally as an easy way to retrieve the current active tracing span
     */
    public static function currentTracingSpan(): ?Span
    {
        return SentrySdk::getCurrentHub()->getSpan();
    }
}
