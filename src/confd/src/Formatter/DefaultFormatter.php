<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Confd\Formatter;

class DefaultFormatter implements FormatterInterface
{
    public function format($value): string
    {
        return match (true) {
            is_int($value) => (string) $value,
            is_array($value) => implode(',', $value),
            default => $value
        };
    }
}
