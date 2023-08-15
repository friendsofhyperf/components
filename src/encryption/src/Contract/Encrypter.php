<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Encryption\Contract;

interface Encrypter
{
    /**
     * Encrypt the given value.
     *
     * @param mixed $value
     * @throws \FriendsOfHyperf\Encryption\Contract\EncryptException
     */
    public function encrypt($value, bool $serialize = true): string;

    /**
     * Decrypt the given value.
     *
     * @return mixed
     * @throws \FriendsOfHyperf\Encryption\Contract\DecryptException
     */
    public function decrypt(string $payload, bool $unserialize = true);

    /**
     * Get the encryption key that the encrypter is currently using.
     *
     * @return string
     */
    public function getKey();
}
