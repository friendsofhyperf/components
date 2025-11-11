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

use FriendsOfHyperf\Sentry\Annotation\IgnoreException;
use FriendsOfHyperf\Sentry\Feature;
use FriendsOfHyperf\Sentry\Monolog\LogsHandler;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\HttpServer\Server as HttpServer;
use Hyperf\RpcServer\Server as RpcServer;
use Hyperf\Server\Event;
use Throwable;

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
        // Enable for tracing integrations
        'sentry.tracing.amqp',
        'sentry.tracing.async_queue',
        'sentry.tracing.command',
        'sentry.tracing.crontab',
        'sentry.tracing.kafka',
        'sentry.tracing.missing_routes',
        'sentry.tracing.request',
        // Enable for tracing Spans
        'sentry.tracing_spans.cache',
        'sentry.tracing_spans.coroutine',
        'sentry.tracing_spans.db',
        'sentry.tracing_spans.elasticsearch',
        'sentry.tracing_spans.filesystem',
        'sentry.tracing_spans.guzzle',
        'sentry.tracing_spans.rpc',
        'sentry.tracing_spans.grpc',
        'sentry.tracing_spans.redis',
        'sentry.tracing_spans.sql_queries',
        'sentry.tracing_spans.view',
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
        $this->compatibilityConfigurations();
        $this->setupRequestLifecycle();
        $this->setupRedisEventEnable();
        $this->setupIgnoreExceptions();
        $this->registerLoggerChannel();
    }

    protected function compatibilityConfigurations(): void
    {
        $mapping = [
            'sentry.tracing.spans' => 'sentry.tracing_spans',
            'sentry.tracing.extra_tags' => 'sentry.tracing_tags',
            'sentry.tracing.enable' => 'sentry.tracing', // MUST be last
        ];

        foreach ($mapping as $oldKey => $newKey) {
            if ($this->config->has($oldKey) && ! $this->config->has($newKey)) {
                $this->config->set($newKey, $this->config->get($oldKey));
                $this->config->set($oldKey, []);
            }
        }
    }

    protected function setupIgnoreExceptions(): void
    {
        $configKey = 'sentry.ignore_exceptions';
        $ignoreExceptions = $this->config->get($configKey, []);
        if (! is_array($ignoreExceptions)) {
            $ignoreExceptions = [];
        }

        /** @var array<class-string<Throwable>,IgnoreException> $classes */
        $classes = AnnotationCollector::getClassesByAnnotation(IgnoreException::class);
        $ignoreExceptions = array_merge($ignoreExceptions, array_keys($classes));

        $this->config->set($configKey, array_unique($ignoreExceptions));
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
            'handler' => [
                'class' => LogsHandler::class,
                'constructor' => [
                    'group' => 'sentry',
                    'logLevel' => $this->config->get('sentry.logs_channel_level'),
                    'bubble' => true,
                ],
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
