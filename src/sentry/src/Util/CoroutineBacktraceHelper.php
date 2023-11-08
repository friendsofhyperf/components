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

use Hyperf\Stringable\Str;

class CoroutineBacktraceHelper
{
    public static function foundCallingOnFunction(): ?string
    {
        $found = false;
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $backtrace) {
            if (Str::startsWith($backtrace['function'], ['Hyperf\Coroutine', 'co', 'go', 'retry'])) {
                continue;
            }
            if (Str::startsWith($backtrace['class'] ?? '', ['Hyperf\Coordinator'])) {
                continue;
            }
            if ($found === true) {
                if (isset($backtrace['class'])) {
                    return sprintf('%s%s%s', $backtrace['class'], $backtrace['type'], $backtrace['function']);
                }
                return $backtrace['function'];
            }
            if (isset($backtrace['class'], $backtrace['function']) && sprintf('%s::%s', $backtrace['class'], $backtrace['function']) === 'Hyperf\Coroutine\Coroutine::create') {
                $found = true;
            }
        }
        return null;
    }
}
