<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Telescope\Telescope;
use FriendsOfHyperf\Telescope\TelescopeConfig;
use Hyperf\Config\Config;

beforeEach(function () {
    $config = new Config([
        'telescope' => [
            'enable' => [
                'request' => true,
                'command' => true,
                'grpc' => true,
                'log' => true,
                'redis' => true,
                'event' => true,
                'exception' => true,
                'job' => true,
                'db' => true,
                'guzzle' => true,
                'cache' => true,
                'rpc' => true,
            ],
            'timezone' => 'Asia/Shanghai',
            'database' => [
                'connection' => 'default',
                'query_slow' => 50,
            ],
            'server' => [
                'enable' => false,
                'host' => '0.0.0.0',
                'port' => 9509,
            ],
            'save_mode' => Telescope::SYNC,
            'ignore_logs' => [
            ],
            'path' => 'telescope',
            'only_paths' => [
                'api/*',
            ],
            'ignore_paths' => [
                'nova-api*',
            ],
        ],
    ]);
    $this->telescopeConfig = new TelescopeConfig($config);
});

test('test isEnable', function ($key, $expected) {
    expect($this->telescopeConfig->isEnable($key))->toBe($expected);
})->with([
    ['request', true],
    ['command', true],
    ['grpc', true],
    ['log', true],
    ['redis', true],
    ['event', true],
    ['exception', true],
    ['job', true],
    ['db', true],
    ['guzzle', true],
    ['cache', true],
    ['rpc', true],
]);

test('test get timezone', function () {
    expect($this->telescopeConfig->getTimezone())->toBe('Asia/Shanghai');
});
