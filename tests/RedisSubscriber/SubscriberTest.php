<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\RedisSubscriber;

use FriendsOfHyperf\Redis\Subscriber\Subscriber;
use FriendsOfHyperf\Tests\TestCase;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Redis;

/**
 * @internal
 * @coversNothing
 */
final class SubscriberTest extends TestCase
{
    protected function setUp(): void
    {
        // $this->markTestSkipped();
        CoordinatorManager::clear(Constants::WORKER_EXIT);
    }

    public function testSubscribe(): void
    {
        $sub = new Subscriber('127.0.0.1', 6379, '', 5);
        $sub->subscribe('foo');
        $sub->subscribe('foo1');
        $sub->unsubscribe('foo');
        defer(fn () => $sub->close());

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
    }

    public function testPsubscribe(): void
    {
        $sub = new Subscriber('127.0.0.1', 6379, '', 5);
        $sub->psubscribe('bar.*');
        $sub->psubscribe('bar1.*');
        $sub->punsubscribe('bar.*');
        defer(fn () => $sub->close());

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
    }
}
