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

interface StringEncrypter
{
    /**
     * Encrypt a string without serialization.
     *
     * @throws EncryptException
     */
    public function encryptString(string $value): string;

    /**
     * Decrypt the given string without unserialization.
     *
     * @throws DecryptException
     */
    public function decryptString(string $payload): string;
}
