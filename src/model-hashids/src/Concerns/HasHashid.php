<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ModelHashids\Concerns;

use Hashids\Hashids;
use Hyperf\Database\Model\Model;

use function Hyperf\Config\config;

/**
 * @method static Model|null findByHashid($hashid)
 * @method static Model findByHashidOrFail($hashid)
 */
trait HasHashid
{
    public const DEFAULT_HASHIDS_CONNECTION = 'main';

    public const DEFAULT_HASHIDS_ALPHABET = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

    public const DEFAULT_HASHIDS_LENGTH = 0;

    public static function bootHasHashid()
    {
        static::addGlobalScope(new HashidScope());
    }

    public function hashid()
    {
        return $this->idToHashid($this->getKey());
    }

    /**
     * Decode the hashid to the id.
     *
     * @param string $hashid
     * @return null|int
     */
    public function hashidToId($hashid)
    {
        return $this->getHashidsClient()->decode($hashid)[0];
    }

    /**
     * Encode an id to its equivalent hashid.
     *
     * @param string $id
     * @return null|string
     */
    public function idToHashid($id)
    {
        return $this->getHashidsClient()->encode($id);
    }

    /**
     * @return Hashids
     */
    protected function getHashidsClient()
    {
        $config = config('hashids.connections.' . $this->getHashidsConnection());

        return new Hashids(
            $config['salt'] ?? '',
            $config['length'] ?? self::DEFAULT_HASHIDS_LENGTH,
            $config['alphabet'] ?? self::DEFAULT_HASHIDS_ALPHABET
        );
    }

    /**
     * @return string
     */
    protected function getHashidsConnection()
    {
        return config('hashids.default', self::DEFAULT_HASHIDS_CONNECTION);
    }

    /**
     * @return null|string
     */
    protected function getHashidAttribute()
    {
        return $this->hashid();
    }
}
