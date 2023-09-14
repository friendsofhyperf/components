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
use Mockery as m;
use Psr\Container\ContainerInterface;

afterEach(function () {
    m::close();
});

function foo(KVInterface $kv): KVInterface
{
    return $kv;
}

test('test KVInterface', function () {
    $KVFactory = m::mock(KVFactory::class, [
        '__invoke' => m::mock(KV::class),
    ]);

    $kv = $KVFactory(m::mock(ContainerInterface::class));

    expect(foo($kv))->toBeInstanceOf(KVInterface::class);
});
