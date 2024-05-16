<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Redis\Subscriber\Subscriber;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;

use function Hyperf\Coroutine\go;

beforeEach(function () {
    CoordinatorManager::clear(Constants::WORKER_EXIT);
});

test('test Subscribe', function () {
    $sub = new Subscriber('127.0.0.1', 6379, '', 5);
    $sub->subscribe('foo');
    $sub->subscribe('foo1');
    $sub->unsubscribe('foo');

    go(function () {
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
        $redis->publish('foo', 'foodata');
        $redis->publish('foo1', 'foo1data');
    });

    $chan = $sub->channel();
    while (true) {
        $data = $chan->pop();
        if (empty($data)) {
            if (! $sub->closed) {
                var_dump('Redis connection is disconnected abnormally');
            }
            break;
        }
        $this->assertEquals($data->channel, 'foo1');
        $this->assertEquals($data->payload, 'foo1data');
        break;
    }

    $sub->close();
});

test('test Psubscribe', function () {
    $sub = new Subscriber('127.0.0.1', 6379, '', 5);
    $sub->psubscribe('bar.*');
    $sub->psubscribe('bar1.*');
    $sub->punsubscribe('bar.*');

    go(function () {
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
        $redis->publish('bar.1', 'bardata');
        $redis->publish('bar1.1', 'bar1data');
    });

    $chan = $sub->channel();
    while (true) {
        $data = $chan->pop();
        if (empty($data)) {
            if (! $sub->closed) {
                var_dump('Redis connection is disconnected abnormally');
            }
            break;
        }
        $this->assertEquals($data->pattern, 'bar1.*');
        $this->assertEquals($data->channel, 'bar1.1');
        $this->assertEquals($data->payload, 'bar1data');
        break;
    }

    $sub->close();
});
