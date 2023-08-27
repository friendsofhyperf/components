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
use Hyperf\Context\ApplicationContext;
use Hyperf\Logger\LoggerFactory;
use Mockery as m;
use Psr\Container\ContainerInterface;

uses()->group('facade');

afterEach(function () {
    m::close();
});

test('test Cache Macroable', function () {
    Cache::macro('test', fn () => null);

    expect(Cache::hasMacro('test'))->toBeTrue();
});

test('test Log Macroable', function () {
    ApplicationContext::setContainer(
        mocking(ContainerInterface::class)->expect(
            get: fn () => mocking(LoggerFactory::class)->expect(
                get: fn () => mocking(\Psr\Log\LoggerInterface::class)->allows()->info('test')->getMock()
            )
        )
    );

    expect(Log::channel('hyperf', 'default'))->toBeInstanceOf(\Psr\Log\LoggerInterface::class);

    expect(Log::info('test'))->toBeEmpty();
});
