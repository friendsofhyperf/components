# Redis Subscriber

Forked from [mix-php/redis-subscriber](https://github.com/mix-php/redis-subscriber)

Redis native protocol subscriber based on Swoole coroutine.

A subscription library based on Swoole coroutine that connects directly to the Redis server using Socket, without relying on the `phpredis` extension. This subscriber has the following advantages:

- **Seamless Modification**: Channels can be added or removed at any time, enabling seamless channel switching.
- **Cross-Coroutine Safe Shutdown**: Subscriptions can be safely closed at any time.
- **Message Retrieval via Channels**: Inspired by the Golang [go-redis](https://github.com/go-redis/redis) library, this package wraps functionality to retrieve subscription messages via channels.

## Installation

```shell
composer require friendsofhyperf/redis-subscriber
```

## Subscribe to Channels

- Exceptions will be thrown in case of connection or subscription failures.

```php
$sub = new \FriendsOfHyperf\Redis\Subscriber\Subscriber('127.0.0.1', 6379, '', 5); // Exception will be thrown if connection fails
$sub->subscribe('foo', 'bar'); // Exception will be thrown if subscription fails

$chan = $sub->channel();
while (true) {
    $data = $chan->pop();
    if (empty($data)) { // Both manual close and abnormal Redis disconnection will return false
        if (!$sub->closed) {
            // Handle abnormal Redis disconnection
            var_dump('Redis connection is disconnected abnormally');
        }
        break;
    }
    var_dump($data);
}
```

When a subscription message is received:

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
| `subscribe(string ...$channels) : void` | Add subscriptions |
| `unsubscribe(string ...$channels) : void` | Remove subscriptions |
| `psubscribe(string ...$channels) : void` | Add pattern-based subscriptions |
| `punsubscribe(string ...$channels) : void` | Remove pattern-based subscriptions |
| `channel() : Hyperf\Engine\Channel` | Get the message channel |
| `close() : void` | Close the subscription |
