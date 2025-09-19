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
            $config['length'] ?? 0,
            $config['alphabet'] ?? 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
        );
    }

    /**
     * @return string
     */
    protected function getHashidsConnection()
    {
        return config('hashids.default', 'main');
    }

    /**
     * @return null|string
     */
    protected function getHashidAttribute()
    {
        return $this->hashid();
    }
}
