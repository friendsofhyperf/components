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
use Throwable;

class Switcher
{
    public function __construct(protected ConfigInterface $config)
    {
    }

    public function isEnable(string $key, bool $default = true): bool
    {
        return (bool) $this->config->get('sentry.enable.' . $key, $default);
    }

    public function isBreadcrumbEnable(string $key, bool $default = true): bool
    {
        return (bool) $this->config->get('sentry.breadcrumbs.' . $key, $default);
    }

    public function isTracingEnable(string $key, bool $default = true): bool
    {
        if (! $this->config->get('sentry.enable_tracing', true)) {
            return false;
        }

        return (bool) $this->config->get('sentry.tracing.enable.' . $key, $default);
    }

    public function isTracingSpanEnable(string $key, bool $default = true): bool
    {
        if (! SentrySdk::getCurrentHub()->getSpan()) {
            return false;
        }

        return (bool) $this->config->get('sentry.tracing.spans.' . $key, $default);
    }

    public function isTracingExtraTagEnable(string $key, bool $default = false): bool
    {
        return (bool) ($this->config->get('sentry.tracing.extra_tags', [])[$key] ?? $default);
    }

    public function isExceptionIgnored(string|Throwable $exception): bool
    {
        $ignoreExceptions = (array) $this->config->get('sentry.ignore_exceptions', []);

        foreach ($ignoreExceptions as $ignoreException) {
            if (is_a($exception, $ignoreException, true)) {
                return true;
            }
        }

        return false;
    }

    public function isCronsEnable(): bool
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
