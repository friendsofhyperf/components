<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */

namespace Friendsofhyperf\Encryption{
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
}

namespace {
    if (! function_exists('decrypt')) {
        /**
         * Decrypt the given value.
         *
         * @return mixed
         * @deprecated since 3.1, please use `\Friendsofhyperf\Encryption\decrypt` instead.
         */
        function decrypt(string $value, bool $unserialize = true)
        {
            return \Friendsofhyperf\Encryption\decrypt($value, $unserialize);
        }
    }

    if (! function_exists('encrypt')) {
        /**
         * Encrypt the given value.
         *
         * @param mixed $value
         * @deprecated since 3.1, please use `\Friendsofhyperf\Encryption\encrypt` instead.
         */
        function encrypt($value, bool $serialize = true): string
        {
            return \Friendsofhyperf\Encryption\encrypt($value, $serialize);
        }
    }
}
