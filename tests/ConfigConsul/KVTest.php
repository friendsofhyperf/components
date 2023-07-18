<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\ConfigConsul\Consul\KVFactory;
use FriendsOfHyperf\ConfigConsul\Consul\KVInterface;
use Mockery as m;
use Psr\Container\ContainerInterface;

test('test KVInterface', function () {
    $KVFactory = m::mock(KVFactory::class, [
        '__invoke' => m::mock(KVInterface::class),
    ]);

    $kv = $KVFactory(m::mock(ContainerInterface::class));

    expect($kv)->toBeInstanceOf(KVInterface::class);
});
