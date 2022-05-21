<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/encryption.
 *
 * @link     https://github.com/friendsofhyperf/encryption
 * @document https://github.com/friendsofhyperf/encryption/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace Friendsofhyperf\Encryption\Contract;

interface StringEncrypter
{
    /**
     * Encrypt a string without serialization.
     *
     * @param string $value
     * @throws \Friendsofhyperf\Encryption\Contract\EncryptException
     * @return string
     */
    public function encryptString($value);

    /**
     * Decrypt the given string without unserialization.
     *
     * @param string $payload
     * @throws \Friendsofhyperf\Encryption\Contract\DecryptException
     * @return string
     */
    public function decryptString($payload);
}
