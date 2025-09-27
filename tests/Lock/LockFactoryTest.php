<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Lock\LockFactory;
use Hyperf\Contract\ConfigInterface;

test('throws exception for invalid driver config', function () {
    $config = $this->mock(ConfigInterface::class, function ($mock) {
        $mock->shouldReceive('has')->with('lock.invalid')->andReturn(false);
    });

    $factory = new LockFactory($config);

    expect(fn () => $factory->make('foo', 0, null, 'invalid'))
        ->toThrow(InvalidArgumentException::class, 'The lock config invalid is invalid.');
});

test('lock factory has config dependency', function () {
    $config = $this->mock(ConfigInterface::class);
    $factory = new LockFactory($config);

    expect($factory)->toBeInstanceOf(LockFactory::class);
});
