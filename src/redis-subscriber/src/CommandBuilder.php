<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Redis\Subscriber;

use Exception;

use function count;
use function is_array;
use function is_int;
use function is_null;
use function is_string;
use function strlen;

class CommandBuilder
{
    /**
     * @param int|string|array<mixed>|null $args
     * @return string the serialized string
     */
    public static function build(mixed $args): string
    {
        if ($args == 'ping') {
            return 'PING' . Constants::CRLF;
        }

        switch (true) {
            case is_null($args):
                return '$-1' . Constants::CRLF;
            case is_int($args):
                return ':' . $args . Constants::CRLF;
            case is_string($args):
                return '$' . strlen($args) . Constants::CRLF . $args . Constants::CRLF;
            case is_array($args):
                $result = '*' . count($args) . Constants::CRLF;
                foreach ($args as $arg) {
                    $result .= static::build($arg);
                }
                return $result;
            default:
                throw new Exception('invalid args');
        }
    }
}
