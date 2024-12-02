# Redis Subscriber

Forked from [mix-php/redis-subscriber](https://github.com/mix-php/redis-subscriber)

Redis native protocol Subscriber based on Swoole coroutine

Using Socket to directly connect to Redis server, without relying on the phpredis extension, this subscriber has the following advantages:

- Smooth modification: You can add or cancel channel subscriptions at any time, implementing seamless channel switching requirements.
- Cross-coroutine safe shutdown: Can close subscription at any time.
- Channel message retrieval: This library's encapsulation style references the golang language [go-redis](https://github.com/go-redis/redis) library, retrieving subscription messages through channels.

## Installation

```shell
composer require friendsofhyperf/redis-subscriber
```

## Subscribe to Channels

- Connection and subscription failures will throw exceptions

```php
$sub = new \FriendsOfHyperf\Redis\Subscriber\Subscriber('127.0.0.1', 6379, '', 5); // Connection failure will throw an exception
$sub->subscribe('foo', 'bar'); // Subscription failure will throw an exception

$chan = $sub->channel();
while (true) {
    $data = $chan->pop();
    if (empty($data)) { // Both manual close and redis abnormal disconnection will cause return false
        if (!$sub->closed) {
            // Redis abnormal disconnection handling
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
| subscribe(string ...$channels) : void | Add subscription |
| unsubscribe(string ...$channels) : void | Cancel subscription |
| psubscribe(string ...$channels) : void | Add pattern subscription |
| punsubscribe(string ...$channels) : void | Cancel pattern subscription |
| channel() : Hyperf\Engine\Channel | Get message channel |
| close() : void | Close subscription |
