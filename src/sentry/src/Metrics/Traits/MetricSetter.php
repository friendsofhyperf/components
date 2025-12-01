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

use Sentry\Unit;

use function FriendsOfHyperf\Sentry\metrics;

trait MetricSetter
{
    protected function trySet(string $prefix, array $metrics, array $stats, int $workerId = 0, ?Unit $unit = null): void
    {
        foreach (array_keys($stats) as $key) {
            $metricsKey = str_replace('.', '_', $prefix . $key);
            if (array_key_exists($metricsKey, $metrics)) {
                metrics()->gauge(
                    $metricsKey,
                    (float) $stats[$key],
                    ['worker' => (string) $workerId],
                    $unit
                );
            }
        }
    }

    // protected function spawnDefaultMetrics()
    // {
    // }
}
