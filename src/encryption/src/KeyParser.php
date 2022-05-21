<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace Friendsofhyperf\Encryption;

use Hyperf\Utils\Str;

class KeyParser
{
    /**
     * Parse the encryption key.
     *
     * @return string
     */
    public function parseKey(array $config)
    {
        if (Str::startsWith($key = $this->key($config), $prefix = 'base64:')) {
            $key = base64_decode(Str::after($key, $prefix));
        }

        return $key;
    }

    /**
     * Extract the encryption key from the given configuration.
     *
     * @throws \RuntimeException
     * @return string
     */
    protected function key(array $config)
    {
        return tap($config['key'] ?? '', function ($key) {
            if (empty($key)) {
                throw new MissingKeyException();
            }
        });
    }
}
