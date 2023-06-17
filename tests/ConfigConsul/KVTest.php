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
use Hyperf\Consul\KV;
use Psr\Container\ContainerInterface;

afterEach(function () {
});

test('test KVInterface', function () {
    $KVFactory = mocking(KVFactory::class)->expect(
        __invoke: fn () => mocking(KV::class)->expect()
    );

    $kv = $KVFactory(mocking(ContainerInterface::class)->expect());

    expect($kv)->toBeInstanceOf(KVInterface::class);
});
