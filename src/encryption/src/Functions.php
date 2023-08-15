<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Encryption;

use Hyperf\Context\ApplicationContext;

/**
 * Decrypt the given value.
 *
 * @return mixed
 */
function decrypt(string $value, bool $unserialize = true)
{
    return ApplicationContext::getContainer()
        ->get(Encrypter::class)
        ->decrypt($value, $unserialize);
}

/**
 * Encrypt the given value.
 *
 * @param mixed $value
 */
function encrypt($value, bool $serialize = true): string
{
    return ApplicationContext::getContainer()
        ->get(Encrypter::class)
        ->encrypt($value, $serialize);
}
