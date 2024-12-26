# Redis Subscriber

Forked from [mix-php/redis-subscriber](https://github.com/mix-php/redis-subscriber)

Redis native protocol Subscriber based on Swoole coroutine

基於 Swoole 協程的 Redis 原生協議訂閲庫

使用 Socket 直接連接 Redis 服務器，不依賴 phpredis 擴展，該訂閲器有如下優點：

- 平滑修改：可隨時增加、取消訂閲通道，實現無縫切換通道的需求。
- 跨協程安全關閉：可在任意時刻關閉訂閲。
- 通道獲取消息：該庫封裝風格參考 golang 語言 [go-redis](https://github.com/go-redis/redis) 庫封裝，通過 channel 獲取訂閲的消息。

## 安裝

```shell
composer require friendsofhyperf/redis-subscriber
```

## 訂閲頻道

- 連接、訂閲失敗會拋出異常

```php
$sub = new \FriendsOfHyperf\Redis\Subscriber\Subscriber('127.0.0.1', 6379, '', 5); // 連接失敗將拋出異常
$sub->subscribe('foo', 'bar'); // 訂閲失敗將拋出異常

$chan = $sub->channel();
while (true) {
    $data = $chan->pop();
    if (empty($data)) { // 手動close與redis異常斷開都會導致返回false
        if (!$sub->closed) {
            // redis異常斷開處理
            var_dump('Redis connection is disconnected abnormally');
        }
        break;
    }
    var_dump($data);
}
```

接收到訂閲消息：

```shell
object(FriendsOfHyperf\Redis\Subscriber\Message)#8 (2) {
  ["channel"]=>
  string(2) "foo"
  ["payload"]=>
  string(4) "test"
}
```

## 全部方法

| 方法 | 描述 |
| --- | --- |
| subscribe(string ...$channels) : void | 增加訂閲 |
| unsubscribe(string ...$channels) : void | 取消訂閲 |
| psubscribe(string ...$channels) : void | 增加通配訂閲 |
| punsubscribe(string ...$channels) : void | 取消通配訂閲 |
| channel() : Hyperf\Engine\Channel | 獲取消息通道 |
| close() : void | 關閉訂閲 |
