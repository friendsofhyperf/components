<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Macros\Macros\Request;

use Carbon\Carbon;

/**
 * @mixin \Hyperf\HttpServer\Request
 */
class Date
{
    public function __invoke()
    {
        return function (string $key, $format = null, $tz = null) {
            /* @phpstan-ignore-next-line */
            if ($this->isNotFilled($key)) {
                return null;
            }

            if (is_null($format)) {
                return Carbon::parse($this->input($key), $tz);
            }

            return Carbon::createFromFormat($format, $this->input($key), $tz);
        };
    }
}
