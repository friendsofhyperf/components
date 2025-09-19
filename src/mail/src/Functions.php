<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Mail;

use BackedEnum;
use FriendsOfHyperf\Support\HtmlString;
use Hyperf\ViewEngine\Contract\Htmlable;

/**
 * Encode HTML special characters in a string.
 *
 * @param null|Htmlable|BackedEnum|string|int|float $value
 * @param bool $doubleEncode
 * @return string
 */
function e($value, $doubleEncode = true)
{
    if ($value instanceof Htmlable) {
        return $value->toHtml();
    }
    if ($value instanceof HtmlString) {
        return $value->toHtml();
    }

    if ($value instanceof BackedEnum) {
        $value = $value->value;
    }

    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', $doubleEncode);
}
