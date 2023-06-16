<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Lock{
    use FriendsOfHyperf\Lock\Driver\LockInterface;
    use Hyperf\Context\ApplicationContext;

    function lock(string $name = null, int $seconds = 0, ?string $owner = null, string $driver = 'default'): LockFactory|LockInterface
    {
        $factory = ApplicationContext::getContainer()->get(LockFactory::class);

        if (is_null($name)) {
            return $factory;
        }

        return $factory->make($name, $seconds, $owner, $driver);
    }
}

namespace {
    use FriendsOfHyperf\Lock\Driver\LockInterface;
    use FriendsOfHyperf\Lock\LockFactory;

    if (! function_exists('lock')) {
        /**
         * @deprecated 3.1, use `\FriendsOfHyperf\Lock\lock()` instead.
         */
        function lock(string $name = null, int $seconds = 0, ?string $owner = null, string $driver = 'default'): LockFactory|LockInterface
        {
            return \FriendsOfHyperf\Lock\lock($name, $seconds, $owner, $driver);
        }
    }
}
