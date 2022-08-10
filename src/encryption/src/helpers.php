<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
if (! function_exists('decrypt')) {
    /**
     * Decrypt the given value.
     *
     * @return mixed
     */
    function decrypt(string $value, bool $unserialize = true)
    {
        return \Hyperf\Utils\ApplicationContext::getContainer()
            ->get(\Friendsofhyperf\Encryption\Encrypter::class)
            ->decrypt($value, $unserialize);
    }
}

if (! function_exists('encrypt')) {
    /**
     * Encrypt the given value.
     *
     * @param mixed $value
     */
    function encrypt($value, bool $serialize = true): string
    {
        return \Hyperf\Utils\ApplicationContext::getContainer()
            ->get(\Friendsofhyperf\Encryption\Encrypter::class)
            ->encrypt($value, $serialize);
    }
}
