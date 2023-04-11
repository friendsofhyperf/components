<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Lock\Driver\LockInterface;
use FriendsOfHyperf\Lock\LockFactory;
use Hyperf\Context\ApplicationContext;

if (! function_exists('lock')) {
    /**
     * @throws TypeError
     * @throws InvalidArgumentException
     */
    function lock(string $name = null, int $seconds = 0, ?string $owner = null, string $driver = 'default'): LockFactory|LockInterface
    {
        /** @var LockFactory $factory */
        $factory = ApplicationContext::getContainer()->get(LockFactory::class);

        if (is_null($name)) {
            return $factory;
        }

        return $factory->make($name, $seconds, $owner, $driver);
    }
}
