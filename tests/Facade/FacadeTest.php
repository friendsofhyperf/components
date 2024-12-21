<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Cache\Repository;
use FriendsOfHyperf\Facade\Log;
use Hyperf\Logger\LoggerFactory;
use Mockery as m;

test()->expect('FriendsOfHyperf\Facade')
    ->classes()
    ->toExtend(FriendsOfHyperf\Facade\Facade::class)
    ->ignoring('FriendsOfHyperf\Facade\ConfigProvider');

test('test Cache Macroable', function () {
    Repository::macro('test', fn () => null);

    expect(Repository::hasMacro('test'))->toBeTrue();
});

test('test Log Macroable', function () {
    $this->mock(
        LoggerFactory::class,
        function ($mock) {
            $mock->shouldReceive('get')->andReturn(m::mock(Psr\Log\LoggerInterface::class, [
                'info' => null,
            ]));
        }
    );

    expect(Log::channel('hyperf', 'default'))->toBeInstanceOf(Psr\Log\LoggerInterface::class);
});
