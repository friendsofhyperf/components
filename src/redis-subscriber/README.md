# Redis Subscriber

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/redis-subscriber)](https://packagist.org/packages/friendsofhyperf/redis-subscriber)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/redis-subscriber)](https://packagist.org/packages/friendsofhyperf/redis-subscriber)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/redis-subscriber)](https://github.com/friendsofhyperf/redis-subscriber)

Forked from [mix-php/redis-subscriber](https://github.com/mix-php/redis-subscriber)

A Redis native protocol Subscriber based on Swoole coroutine

A Redis native protocol subscription library based on Swoole coroutine

It connects directly to the Redis server using a Socket, independent of the phpredis extension. This subscriber has the following advantages:

- Smooth modification: Subscriptions can be added or canceled at any time, fulfilling the need for seamless channel switching.
- Safe closure across coroutines: Subscription can be closed at any moment.
- Channel message retrieval: This library's encapsulation style is inspired by the [go-redis](https://github.com/go-redis/redis) library in the Go language, retrieving subscribed messages through a channel.

## Installation

```shell
composer require friendsofhyperf/redis-subscriber
```

## Subscribing to Channels

- Connection or subscription failures will throw an exception

```php
$sub = new \FriendsOfHyperf\Redis\Subscriber\Subscriber('127.0.0.1', 6379, '', 5); // Connection failure will throw an exception
$sub->subscribe('foo', 'bar'); // Subscription failure will throw an exception

$chan = $sub->channel();
while (true) {
    $data = $chan->pop();
    if (empty($data)) { // Manual close or abnormal disconnection from Redis will return false
        if (!$sub->closed) {
            // Handle abnormal disconnection from Redis
            var_dump('Redis connection is disconnected abnormally');
        }
        break;
    }
    var_dump($data);
}
```

Receiving subscribed messages:

```shell
object(FriendsOfHyperf\Redis\Subscriber\Message)#8 (2) {
  ["channel"]=>
  string(2) "foo"
  ["payload"]=>
  string(4) "test"
}
```

## All Methods

| Method | Description |
| --- | --- |
| subscribe(string ...$channels) : void | Add subscriptions |
| unsubscribe(string ...$channels) : void | Cancel subscriptions |
| psubscribe(string ...$channels) : void | Add pattern subscriptions |
| punsubscribe(string ...$channels) : void | Cancel pattern subscriptions |
| channel() : Hyperf\Engine\Channel | Retrieve the message channel |
| close() : void | Close the subscription |

## License

MIT
