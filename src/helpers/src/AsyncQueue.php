<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Helpers\AsyncQueue;

use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\JobInterface;

use function FriendsOfHyperf\Helpers\di;

function dispatch(JobInterface $job, ?string $pool = null, ?int $delay = null, ?int $maxAttempts = null): bool
{
    if (! is_null($maxAttempts)) {
        (function ($maxAttempts) {
            if (property_exists($this, 'maxAttempts')) {
                $this->maxAttempts = $maxAttempts;
            }
        })->call($job, $maxAttempts);
    }

    $pool = $pool ?? (fn () => $this->pool ?? null)->call($job) ?? 'default'; // @phpstan-ignore-line
    $delay = $delay ?? (fn () => $this->delay ?? null)->call($job) ?? 0; // @phpstan-ignore-line

    return di(DriverFactory::class)->get($pool)->push($job, $delay);
}
