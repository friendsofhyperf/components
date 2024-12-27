# Redis Subscriber

基於 Swoole 協程的 Redis 原生協議訂閱器，程式碼從 [mix-php/redis-subscriber](https://github.com/mix-php/redis-subscriber) 復刻而來。

基於 Swoole 協程的 Redis 原生協議訂閱庫，使用 Socket 直接連線 Redis 伺服器，不依賴 phpredis 擴充套件，該訂閱器有如下優點：

- 平滑修改：可隨時增加、取消訂閱通道，實現無縫切換通道的需求。
- 跨協程安全關閉：可在任意時刻關閉訂閱。
- 通道獲取訊息：該庫封裝風格參考 golang 語言 [go-redis](https://github.com/go-redis/redis) 庫封裝，透過 channel 獲取訂閱的訊息。

## 安裝

```shell
composer require friendsofhyperf/redis-subscriber
```

## 訂閱頻道

- 連線、訂閱失敗會丟擲異常

```php
$sub = new \FriendsOfHyperf\Redis\Subscriber\Subscriber('127.0.0.1', 6379, '', 5); // 連線失敗將丟擲異常
$sub->subscribe('foo', 'bar'); // 訂閱失敗將丟擲異常

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

接收到訂閱訊息：

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
| subscribe(string ...$channels) : void | 增加訂閱 |
| unsubscribe(string ...$channels) : void | 取消訂閱 |
| psubscribe(string ...$channels) : void | 增加通配訂閱 |
| punsubscribe(string ...$channels) : void | 取消通配訂閱 |
| channel() : Hyperf\Engine\Channel | 獲取訊息通道 |
| close() : void | 關閉訂閱 |
