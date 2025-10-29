<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Cache\CacheManager;
use FriendsOfHyperf\Cache\Contract\Factory;

use function PHPStan\Testing\assertType;

/** @var CacheManager $manager */
$manager = null;

/** @var Factory $factory */
$factory = null;

// CacheManager::store() tests
assertType('FriendsOfHyperf\Cache\Contract\Repository', $manager->store());
assertType('FriendsOfHyperf\Cache\Contract\Repository', $manager->store('default'));
assertType('FriendsOfHyperf\Cache\Contract\Repository', $manager->store('redis'));

// CacheManager::driver() tests
assertType('FriendsOfHyperf\Cache\Contract\Repository', $manager->driver());
assertType('FriendsOfHyperf\Cache\Contract\Repository', $manager->driver('default'));

// CacheManager::resolve() tests
assertType('FriendsOfHyperf\Cache\Contract\Repository', $manager->resolve('default'));

// Factory::store() tests
assertType('FriendsOfHyperf\Cache\Contract\Repository', $factory->store());
assertType('FriendsOfHyperf\Cache\Contract\Repository', $factory->store('default'));
