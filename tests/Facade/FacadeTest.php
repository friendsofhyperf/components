<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Cache\Cache;
use FriendsOfHyperf\Facade\Log;
use Hyperf\Logger\LoggerFactory;
use Mockery as m;

uses()->group('facade');

test('test Cache Macroable', function () {
    Cache::macro('test', fn () => null);

    expect(Cache::hasMacro('test'))->toBeTrue();
});

test('test Log Macroable', function () {
    $this->instance(
        LoggerFactory::class,
        m::mock(LoggerFactory::class, [
            'get' => m::mock(\Psr\Log\LoggerInterface::class, [
                'info' => null,
            ]),
        ])
    );

    expect(Log::channel('hyperf', 'default'))->toBeInstanceOf(\Psr\Log\LoggerInterface::class);

    /* @phpstan-ignore-next-line */
    expect(Log::info('test'))->toBeEmpty();
});
