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

    public function isEnabled(string $key, bool $default = true): bool
    {
        return (bool) $this->config->get('sentry.enable.' . $key, $default);
    }

    /**
     * @deprecated since v3.1, use isEnabled instead, will be removed in v3.2
     */
    public function isEnable(string $key, bool $default = true): bool
    {
        return $this->isEnabled($key, $default);
    }

    public function isBreadcrumbEnabled(string $key, bool $default = true): bool
    {
        return (bool) $this->config->get('sentry.breadcrumbs.' . $key, $default);
    }

    /**
     * @deprecated since v3.1, use isBreadcrumbEnabled instead, will be removed in v3.2
     */
    public function isBreadcrumbEnable(string $key, bool $default = true): bool
    {
        return $this->isBreadcrumbEnabled($key, $default);
    }

    public function isTracingEnabled(string $key, bool $default = true): bool
    {
        if (! $this->config->get('sentry.enable_tracing', true)) {
            return false;
        }

        return (bool) $this->config->get('sentry.tracing.enable.' . $key, $default);
    }

    /**
     * @deprecated since v3.1, use isTracingEnabled instead, will be removed in v3.2
     */
    public function isTracingEnable(string $key, bool $default = true): bool
    {
        return $this->isTracingEnabled($key, $default);
    }

    public function isTracingSpanEnabled(string $key, bool $default = true): bool
    {
        if (! SentrySdk::getCurrentHub()->getSpan()) {
            return false;
        }

        return (bool) $this->config->get('sentry.tracing.spans.' . $key, $default);
    }

    /**
     * @deprecated since v3.1, use isTracingSpanEnabled instead, will be removed in v3.2
     */
    public function isTracingSpanEnable(string $key, bool $default = true): bool
    {
        return $this->isTracingSpanEnabled($key, $default);
    }

    public function isTracingExtraTagEnabled(string $key, bool $default = false): bool
    {
        return (bool) ($this->config->get('sentry.tracing.extra_tags', [])[$key] ?? $default);
    }

    /**
     * @deprecated since v3.1, use isTracingExtraTagEnabled instead, will be removed in v3.2
     */
    public function isTracingExtraTagEnable(string $key, bool $default = false): bool
    {
        return $this->isTracingExtraTagEnabled($key, $default);
    }

    /**
     * @deprecated since v3.1, use isExceptionIgnored instead, will be removed in v3.2
     * @see https://docs.sentry.io/platforms/php/configuration/options/#ignore_exceptions
     */
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

    public function isCronsEnabled(): bool
    {
        return (bool) $this->config->get('sentry.crons.enable', true);
    }

    /**
     * @deprecated since v3.1, use isCronsEnabled instead, will be removed in v3.2
     */
    public function isCronsEnable(): bool
    {
        return $this->isCronsEnabled();
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
