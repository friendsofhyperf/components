<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/encryption.
 *
 * @link     https://github.com/friendsofhyperf/encryption
 * @document https://github.com/friendsofhyperf/encryption/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace Friendsofhyperf\Encryption;

use RuntimeException;

class MissingKeyException extends RuntimeException
{
    /**
     * Create a new exception instance.
     *
     * @param string $message
     */
    public function __construct($message = 'No application encryption key has been specified.')
    {
        parent::__construct($message);
    }
}
