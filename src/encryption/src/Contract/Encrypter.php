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
     * @throws EncryptException
     */
    public function encrypt($value, bool $serialize = true): string;

    /**
     * Decrypt the given value.
     *
     * @return mixed
     * @throws DecryptException
     */
    public function decrypt(string $payload, bool $unserialize = true);

    /**
     * Get the encryption key that the encrypter is currently using.
     *
     * @return string
     */
    public function getKey();

    /**
     * Get the current encryption key and all previous encryption keys.
     *
     * @return array
     */
    public function getAllKeys();

    /**
     * Get the previous encryption keys.
     *
     * @return array
     */
    public function getPreviousKeys();
}
