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

use Hyperf\Contract\ConfigInterface;
use Sentry\SentrySdk;
use Throwable;

class Switcher
{
    public function __construct(protected ConfigInterface $config)
    {
    }

    public function isEnable(string $key): bool
    {
        return (bool) $this->config->get('sentry.enable.' . $key, false);
    }

    public function isBreadcrumbEnable(string $key): bool
    {
        return (bool) $this->config->get('sentry.breadcrumbs.' . $key, false);
    }

    public function isTracingEnable(string $key): bool
    {
        if (! SentrySdk::getCurrentHub()->getSpan()) {
            return false;
        }

        return (bool) $this->config->get('sentry.tracing.enable.' . $key, false);
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
}
