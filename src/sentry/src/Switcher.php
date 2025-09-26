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
 * @deprecated since v3.1, use Feature instead, will be removed in v3.2
 */
class Switcher extends Feature
{
    /**
     * @deprecated since v3.1, use Feature::isEnabled() instead, will be removed in v3.2
     */
    public function isEnable(string $key, bool $default = true): bool
    {
        return $this->isEnabled($key, $default);
    }

    /**
     * @deprecated since v3.1, use Feature::isBreadcrumbEnabled() instead, will be removed in v3.2
     */
    public function isBreadcrumbEnable(string $key, bool $default = true): bool
    {
        return $this->isBreadcrumbEnabled($key, $default);
    }

    /**
     * @deprecated since v3.1, use Feature::isTracingEnabled() instead, will be removed in v3.2
     */
    public function isTracingEnable(string $key, bool $default = true): bool
    {
        return $this->isTracingEnabled($key, $default);
    }

    /**
     * @deprecated since v3.1, use Feature::isTracingSpanEnabled() instead, will be removed in v3.2
     */
    public function isTracingSpanEnable(string $key, bool $default = true): bool
    {
        return $this->isTracingSpanEnabled($key, $default);
    }

    /**
     * @deprecated since v3.1, use Feature::isTracingExtraTagEnabled() instead, will be removed in v3.2
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

    /**
     * @deprecated since v3.1, use Feature::isCronsEnabled() instead, will be removed in v3.2
     */
    public function isCronsEnable(): bool
    {
        return $this->isCronsEnabled();
    }
}
