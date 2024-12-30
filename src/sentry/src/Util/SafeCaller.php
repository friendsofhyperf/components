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

use Closure;
use Sentry\SentrySdk;
use Throwable;

use function Hyperf\Support\value;

class SafeCaller
{
    /**
     * Example
     * $data['scores'] = di(FriendsOfHyperf\Sentry\Util\SafeCaller::class)->call(fn () => time(), 'default');.
     *
     * @template TReturn
     *
     * @param Closure(): TReturn $closure
     * @return TReturn|mixed
     */
    public function call(Closure $closure, mixed $default = null, ?Closure $exceptionHandler = null): mixed
    {
        try {
            return $closure();
        } catch (Throwable $e) {
            $report = true;

            if ($exceptionHandler) { // Do not capture when the $exceptionHandler returns false
                $report = $exceptionHandler($e);
            }

            $report !== false && SentrySdk::getCurrentHub()->captureException($e);

            return value($default);
        }
    }
}
