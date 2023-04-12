<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
use Friendsofhyperf\Encryption\Encrypter;
use Hyperf\Context\ApplicationContext;

if (! function_exists('decrypt')) {
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
}

if (! function_exists('encrypt')) {
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
}
