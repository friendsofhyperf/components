# Redis Subscriber

A Redis native protocol subscriber based on Swoole coroutines, forked from [mix-php/redis-subscriber](https://github.com/mix-php/redis-subscriber).

This Redis subscription library, built on Swoole coroutines, connects directly to the Redis server using sockets without relying on the phpredis extension. The subscriber offers the following advantages:

- **Smooth Modifications**: You can add or cancel subscription channels at any time, enabling seamless channel switching.
- **Cross-Coroutine Safe Shutdown**: The subscription can be closed at any moment safely.
- **Channel Message Retrieval**: The library's encapsulation style is inspired by the [go-redis](https://github.com/go-redis/redis) library in Golang, allowing you to retrieve subscribed messages through channels.

## Installation

```shell
composer require friendsofhyperf/redis-subscriber
```

## Subscribing to Channels

- Connection and subscription failures will throw exceptions.

```php
$sub = new \FriendsOfHyperf\Redis\Subscriber\Subscriber('127.0.0.1', 6379, '', 5); // Throws an exception if connection fails
$sub->subscribe('foo', 'bar'); // Throws an exception if subscription fails

$chan = $sub->channel();
while (true) {
    $data = $chan->pop();
    if (empty($data)) { // Returns false if manually closed or if the Redis connection is abnormally disconnected
        if (!$sub->closed) {
            // Handle abnormal Redis disconnection
            var_dump('Redis connection is disconnected abnormally');
        }
        break;
    }
    var_dump($data);
}
```

Received subscription message:

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
| channel() : Hyperf\Engine\Channel | Get the message channel |
| close() : void | Close the subscription |