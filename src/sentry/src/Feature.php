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

use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Sentry\SentrySdk;

class Feature
{
    public function __construct(protected ConfigInterface $config)
    {
    }

    public function isEnabled(string $key, bool $default = true): bool
    {
        return (bool) $this->config->get('sentry.enable.' . $key, $default);
    }

    public function isBreadcrumbEnabled(string $key, bool $default = true): bool
    {
        return (bool) $this->config->get('sentry.breadcrumbs.' . $key, $default);
    }

    public function isMetricsEnabled(bool $default = true): bool
    {
        return (bool) $this->config->get('sentry.enable_metrics', $default);
    }

    public function isDefaultMetricsEnabled(bool $default = true): bool
    {
        if (! $this->isMetricsEnabled()) {
            return false;
        }

        return (bool) $this->config->get('sentry.enable_default_metrics', $default);
    }

    public function isCommandMetricsEnabled(bool $default = true): bool
    {
        if (! $this->isMetricsEnabled()) {
            return false;
        }

        return (bool) $this->config->get('sentry.enable_command_metrics', $default);
    }

    public function getMetricsInterval(int $default = 10): int
    {
        $interval = (int) $this->config->get('sentry.metrics_interval', $default);

        if ($interval < 5) {
            return 5;
        }

        return $interval;
    }

    public function isTracingEnabled(string $key, bool $default = true): bool
    {
        return (bool) $this->config->get('sentry.tracing.' . $key, $default);
    }

    public function isTracingSpanEnabled(string $key, bool $default = true): bool
    {
        if (! SentrySdk::getCurrentHub()->getSpan()) {
            return false;
        }

        return (bool) $this->config->get('sentry.tracing_spans.' . $key, $default);
    }

    public function isTracingTagEnabled(string $key, bool $default = false): bool
    {
        return (bool) ($this->config->get('sentry.tracing_tags', [])[$key] ?? $default);
    }

    /**
     * @deprecated since v3.1, will be removed in v3.2, use `isTracingTagEnabled` instead.
     */
    public function isTracingExtraTagEnabled(string $key, bool $default = false): bool
    {
        return $this->isTracingTagEnabled($key, $default);
    }

    public function isCronsEnabled(): bool
    {
        return (bool) $this->config->get('sentry.crons.enable', true);
    }

    public static function disableCoroutineTracing(): void
    {
        Context::set(Constants::DISABLE_COROUTINE_TRACING, true);
    }

    public static function isDisableCoroutineTracing(): bool
    {
        return (bool) Context::get(Constants::DISABLE_COROUTINE_TRACING);
    }
}
