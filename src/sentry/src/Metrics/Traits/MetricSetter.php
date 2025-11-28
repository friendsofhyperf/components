<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Metrics\Traits;

use Sentry\Metrics\TraceMetrics;
use Sentry\Unit;

trait MetricSetter
{
    protected function trySet(string $prefix, array $metrics, array $stats, int $workerId = 0, ?Unit $unit = null): void
    {
        foreach (array_keys($stats) as $key) {
            $metricsKey = str_replace('.', '_', $prefix . $key);
            if (array_key_exists($metricsKey, $metrics)) {
                TraceMetrics::getInstance()->gauge(
                    $metricsKey,
                    (float) $stats[$key],
                    ['worker' => (string) $workerId],
                    $unit
                );
            }
        }
    }
}
