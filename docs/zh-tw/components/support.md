# Support

Support 元件提供 FriendsOfHyperf 各元件共用的工具，包括流暢派送 API、可序列化閉包工作和
重試退避策略。

## 安裝

```shell
composer require friendsofhyperf/support
```

AMQP、Kafka 和非同步佇列整合為選用功能。派送對應訊息類型前，請先安裝相應的 Hyperf 元件。

## 流暢派送

`dispatch()` 輔助函式會依照傳入值的類型選擇待派送物件：

- `Closure` 會建立 `CallQueuedClosure` 並使用非同步佇列派送器。
- `ProducerMessageInterface` 使用 AMQP 生產者派送器。
- `ProduceMessage` 使用 Kafka 生產者派送器。
- 其他值會被拒絕，並拋出 `InvalidArgumentException`。

### 非同步佇列

```php
use function FriendsOfHyperf\Support\dispatch;

dispatch(function (UserService $users) {
    $users->synchronize();
})
    ->onPool('default')
    ->delay(30)
    ->setMaxAttempts(3);
```

閉包支援相依性注入。待派送物件會在銷毀時派送工作。

### AMQP 與 Kafka

```php
dispatch($amqpMessage)
    ->onPool('default')
    ->setExchange('events')
    ->setRoutingKey('user.updated')
    ->setTimeout(5)
    ->setConfirm(true);

dispatch($kafkaMessage)->onPool('default');
```

### 條件設定

所有待派送物件都支援 `when()` 和 `unless()`：

```php
dispatch($job)
    ->when($highPriority, fn ($pending) => $pending->onPool('high-priority'))
    ->unless($canRunNow, fn ($pending) => $pending->delay(60));
```

## 閉包工作

需要將閉包工作傳遞給其他 API 時，可以直接建立可序列化的非同步佇列工作：

```php
use FriendsOfHyperf\Support\CallQueuedClosure;

$job = CallQueuedClosure::create(function () {
    return 'completed';
});
$job->setMaxAttempts(3);
```

## 退避策略

退避策略實作會回傳下一次等待的毫秒數，並提供 `next()`、`reset()`、`getAttempt()` 和
`sleep()` 方法。

### 陣列退避

```php
use FriendsOfHyperf\Support\Backoff\ArrayBackoff;

$custom = new ArrayBackoff([100, 500, 1000, 2000]);
$short = ArrayBackoff::fromPattern('short');
$fromString = ArrayBackoff::fromString('100, 500, 1000');
```

如果希望陣列用完後回傳 `0`，而不是重複最後一個值，請將建構函式第二個參數設為 `false`。

### 可用策略

- `FixedBackoff`：固定等待時間。
- `LinearBackoff`：依固定步長增加等待時間。
- `ExponentialBackoff`：指數成長，可選用抖動。
- `FibonacciBackoff`：使用費波那契數列等待時間。
- `PoissonBackoff`：依泊松分布產生等待時間。
- `DecorrelatedJitterBackoff`：為分散式重試提供去相關抖動。
- `ArrayBackoff`：使用呼叫端定義的等待序列。

## 其他工具

此元件還包含 `retry()` 和 `once()` 輔助函式，以及 `ConfigurationUrlParser`、`Env`、
`HtmlString`、`Number`、`Once`、`Sleep`、`Timebox`、`UuidContainer`、`RedisCommand`、
管線輔助功能和可重用 Trait。在其他元件新增重複工具前，請先檢查
`FriendsOfHyperf\Support` 下已有的類別。
