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

use FriendsOfHyperf\Sentry\Switcher;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\HttpServer\Server as HttpServer;
use Hyperf\RpcServer\Server as RpcServer;
use Hyperf\Server\Event;

class SetRequestLifecycleListener implements ListenerInterface
{
    public function __construct(
        protected ConfigInterface $config,
        protected Switcher $switcher
    ) {
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        $keys = [
            'sentry.enable.amqp',
            'sentry.enable.async_queue',
            'sentry.enable.command',
            'sentry.enable.crontab',
            'sentry.enable.kafka',
            'sentry.enable.request',
            'sentry.breadcrumbs.cache',
            'sentry.breadcrumbs.sql_queries',
            'sentry.breadcrumbs.sql_bindings',
            'sentry.breadcrumbs.sql_transaction',
            'sentry.breadcrumbs.redis',
            'sentry.breadcrumbs.guzzle',
            'sentry.breadcrumbs.logs',
            'sentry.enable_tracing',
            'sentry.tracing.enable.amqp',
            'sentry.tracing.enable.async_queue',
            'sentry.tracing.enable.command',
            'sentry.tracing.enable.crontab',
            'sentry.tracing.enable.kafka',
            'sentry.tracing.enable.request',
            'sentry.tracing.spans.coroutine',
            'sentry.tracing.spans.db',
            'sentry.tracing.spans.elasticsearch',
            'sentry.tracing.spans.guzzle',
            'sentry.tracing.spans.rpc',
            'sentry.tracing.spans.redis',
            'sentry.tracing.spans.sql_queries',
        ];

        foreach ($keys as $key) {
            if (! $this->config->has($key)) {
                $this->config->set($key, true);
            }
        }

        if (
            ! $this->switcher->isEnable('request')
            && ! $this->switcher->isTracingEnable('request')
        ) {
            return;
        }

        $servers = $this->config->get('server.servers', []);

        foreach ($servers as &$server) {
            $callbacks = $server['callbacks'] ?? [];
            $handler = $callbacks[Event::ON_REQUEST][0] ?? $callbacks[Event::ON_RECEIVE][0] ?? null;

            if (! $handler) {
                continue;
            }

            if (
                is_a($handler, HttpServer::class, true)
                || is_a($handler, RpcServer::class, true)
            ) {
                $server['options'] ??= [];
                $server['options']['enable_request_lifecycle'] = true;
            }
        }

        $this->config->set('server.servers', $servers);
    }
}
