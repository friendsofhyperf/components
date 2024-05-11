<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Util;

class SafeCaller
{
    /**
     * Example
     * $data['scores'] = SentrySdk::capture(fn () => di(ScoresInterface::class)->get(), 'default');.
     */
    public function capture(Closure $closure, mixed $default = null, ?Closure $exceptionHandle = null): mixed
    {
        try {
            return $closure();
        } catch (Throwable $e) {
            if ($exceptionHandle) {
                $exceptionHandle($e);
            }

            SentrySdk::getCurrentHub()->captureException($e);

            return value($default);
        }
    }
}
