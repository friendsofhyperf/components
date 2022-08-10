<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace Friendsofhyperf\Encryption;

use Hyperf\Utils\Str;

class KeyParser
{
    /**
     * Parse the encryption key.
     */
    public function parseKey(array $config): string
    {
        if (str_starts_with($key = $this->key($config), $prefix = 'base64:')) {
            $key = base64_decode(Str::after($key, $prefix));
        }

        return $key;
    }

    /**
     * Extract the encryption key from the given configuration.
     *
     * @throws \RuntimeException
     */
    protected function key(array $config): string
    {
        return tap($config['key'] ?? '', function ($key) {
            if (empty($key)) {
                throw new MissingKeyException();
            }
        });
    }
}
