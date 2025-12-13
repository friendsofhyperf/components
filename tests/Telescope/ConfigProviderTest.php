<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Telescope\ConfigProvider;

test('ConfigProvider includes all required listeners', function () {
    $configProvider = new ConfigProvider();
    $config = $configProvider();

    expect($config)->toHaveKey('listeners')
        ->and($config['listeners'])->toBeArray()
        ->and($config['listeners'])->toContain(
            FriendsOfHyperf\Telescope\Listener\RequestHandledListener::class,
            FriendsOfHyperf\Telescope\Listener\SetRequestLifecycleListener::class,
            FriendsOfHyperf\Telescope\Listener\ExceptionHandlerListener::class,
            FriendsOfHyperf\Telescope\Listener\CommandListener::class,
            FriendsOfHyperf\Telescope\Listener\CronEventListener::class,
            FriendsOfHyperf\Telescope\Listener\DbQueryListener::class,
            FriendsOfHyperf\Telescope\Listener\FetchRecordingOnBootListener::class,
            FriendsOfHyperf\Telescope\Listener\RedisCommandExecutedListener::class
        );
});

test('ConfigProvider includes RegisterRoutesListener with priority', function () {
    $configProvider = new ConfigProvider();
    $config = $configProvider();

    expect($config['listeners'])->toHaveKey(FriendsOfHyperf\Telescope\Listener\RegisterRoutesListener::class)
        ->and($config['listeners'][FriendsOfHyperf\Telescope\Listener\RegisterRoutesListener::class])->toBe(-1);
});
