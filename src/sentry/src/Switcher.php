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

use Throwable;

/**
 * @deprecated since v3.1, use `Feature` instead, will be removed in v3.2
 */
class Switcher extends Feature
{
    public function isEnable(string $key, bool $default = true): bool
    {
        return $this->isEnabled($key, $default);
    }

    public function isBreadcrumbEnable(string $key, bool $default = true): bool
    {
        return $this->isBreadcrumbEnabled($key, $default);
    }

    public function isTracingEnable(string $key, bool $default = true): bool
    {
        return $this->isTracingEnabled($key, $default);
    }

    public function isTracingSpanEnable(string $key, bool $default = true): bool
    {
        return $this->isTracingSpanEnabled($key, $default);
    }

    public function isTracingExtraTagEnable(string $key, bool $default = false): bool
    {
        return $this->isTracingTagEnabled($key, $default);
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
        return $this->isCronsEnabled();
    }
}
