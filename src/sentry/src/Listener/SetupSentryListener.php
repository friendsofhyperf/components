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

use FriendsOfHyperf\Sentry\Feature;
use FriendsOfHyperf\Sentry\Monolog\LogsHandler;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\HttpServer\Server as HttpServer;
use Hyperf\RpcServer\Server as RpcServer;
use Hyperf\Server\Event;

class SetupSentryListener implements ListenerInterface
{
    protected const MISSING_KEYS = [
        // Enable
        'sentry.enable.amqp',
        'sentry.enable.async_queue',
        'sentry.enable.command',
        'sentry.enable.crontab',
        'sentry.enable.kafka',
        'sentry.enable.request',
        // Breadcrumbs
        'sentry.breadcrumbs.cache',
        'sentry.breadcrumbs.sql_queries',
        'sentry.breadcrumbs.sql_bindings',
        'sentry.breadcrumbs.sql_transaction',
        'sentry.breadcrumbs.redis',
        'sentry.breadcrumbs.guzzle',
        'sentry.breadcrumbs.logs',
        // Tracing
        'sentry.enable_tracing',
        // Enable for tracing integrations
        'sentry.tracing.enable.amqp',
        'sentry.tracing.enable.async_queue',
        'sentry.tracing.enable.cache',
        'sentry.tracing.enable.command',
        'sentry.tracing.enable.crontab',
        'sentry.tracing.enable.kafka',
        'sentry.tracing.enable.request',
        // Enable for tracing Spans
        'sentry.tracing.spans.cache',
        'sentry.tracing.spans.coroutine',
        'sentry.tracing.spans.db',
        'sentry.tracing.spans.elasticsearch',
        'sentry.tracing.spans.guzzle',
        'sentry.tracing.spans.rpc',
        'sentry.tracing.spans.redis',
        'sentry.tracing.spans.sql_queries',
    ];

    public function __construct(
        protected ConfigInterface $config,
        protected Feature $feature,
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
        $this->setupRequestLifecycle();
        $this->setupRedisEventEnable();
        $this->registerLoggerChannel();
    }

    protected function registerLoggerChannel(): void
    {
        if (
            ! $this->config->get('sentry.enable_logs', true)
            || $this->config->has('logger.sentry')
        ) {
            return;
        }

        $this->config->set('logger.sentry', [
            'handler' => LogsHandler::class,
            'constructor' => [
                'group' => 'sentry',
                'logLevel' => $this->config->get('sentry.logs_channel_level'),
                'bubble' => true,
            ],
            'formatter' => null,
            'processors' => [],
        ]);
    }

    protected function setupRequestLifecycle(): void
    {
        foreach (self::MISSING_KEYS as $key) {
            if (! $this->config->has($key)) {
                $this->config->set($key, true);
            }
        }

        if (
            ! $this->feature->isEnabled('request')
            && ! $this->feature->isTracingEnabled('request')
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

            if (is_a($handler, HttpServer::class, true) || is_a($handler, RpcServer::class, true)) {
                $server['options'] ??= [];
                $server['options']['enable_request_lifecycle'] = true;
            }
        }

        $this->config->set('server.servers', $servers);
    }

    protected function setupRedisEventEnable(): void
    {
        if (! $this->config->has('redis')) {
            return;
        }

        foreach ($this->config->get('redis', []) as $pool => $_) {
            $this->config->set("redis.{$pool}.event.enable", true);
        }
    }
}
