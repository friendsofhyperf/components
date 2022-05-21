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

interface Encrypter
{
    /**
     * Encrypt the given value.
     *
     * @param mixed $value
     * @param bool $serialize
     * @throws \Friendsofhyperf\Encryption\Contract\EncryptException
     * @return string
     */
    public function encrypt($value, $serialize = true);

    /**
     * Decrypt the given value.
     *
     * @param string $payload
     * @param bool $unserialize
     * @throws \Friendsofhyperf\Encryption\Contract\DecryptException
     * @return mixed
     */
    public function decrypt($payload, $unserialize = true);
}
