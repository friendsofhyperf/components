<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
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
     * @return string
     * @throws \Friendsofhyperf\Encryption\Contract\EncryptException
     */
    public function encrypt($value, $serialize = true);

    /**
     * Decrypt the given value.
     *
     * @param string $payload
     * @param bool $unserialize
     * @return mixed
     * @throws \Friendsofhyperf\Encryption\Contract\DecryptException
     */
    public function decrypt($payload, $unserialize = true);
}
