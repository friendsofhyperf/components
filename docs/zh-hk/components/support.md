# Support

Support 組件提供 FriendsOfHyperf 各組件共享的工具，包括流式派發 API、可序列化閉包任務和
重試退避策略。

## 安裝

```shell
composer require friendsofhyperf/support
```

AMQP、Kafka 和異步隊列集成為可選功能。派發對應消息類型前，請先安裝相應的 Hyperf 組件。

## 流式派發

`dispatch()` 輔助函數會根據傳入值的類型選擇待派發對象：

- `Closure` 會創建 `CallQueuedClosure` 並使用異步隊列派發器。
- `ProducerMessageInterface` 使用 AMQP 生產者派發器。
- `ProduceMessage` 使用 Kafka 生產者派發器。
- 其他值會被拒絕，並拋出 `InvalidArgumentException`。

### 異步隊列

```php
use function FriendsOfHyperf\Support\dispatch;

dispatch(function (UserService $users) {
    $users->synchronize();
})
    ->onPool('default')
    ->delay(30)
    ->setMaxAttempts(3);
```

閉包支持依賴注入。待派發對象會在銷燬時派發任務。

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

### 條件配置

所有待派發對象都支持 `when()` 和 `unless()`：

```php
dispatch($job)
    ->when($highPriority, fn ($pending) => $pending->onPool('high-priority'))
    ->unless($canRunNow, fn ($pending) => $pending->delay(60));
```

## 閉包任務

需要將閉包任務傳遞給其他 API 時，可以直接創建可序列化的異步隊列任務：

```php
use FriendsOfHyperf\Support\CallQueuedClosure;

$job = CallQueuedClosure::create(function () {
    return 'completed';
});
$job->setMaxAttempts(3);
```

## 退避策略

退避策略實現會返回下一次等待的毫秒數，並提供 `next()`、`reset()`、`getAttempt()` 和
`sleep()` 方法。

### 數組退避

```php
use FriendsOfHyperf\Support\Backoff\ArrayBackoff;

$custom = new ArrayBackoff([100, 500, 1000, 2000]);
$short = ArrayBackoff::fromPattern('short');
$fromString = ArrayBackoff::fromString('100, 500, 1000');
```

如果希望數組耗盡後返回 `0`，而不是重複最後一個值，請將構造函數第二個參數設為 `false`。

### 可用策略

- `FixedBackoff`：固定等待時間。
- `LinearBackoff`：按固定步長增加等待時間。
- `ExponentialBackoff`：指數增長，可選抖動。
- `FibonacciBackoff`：使用斐波那契數列等待時間。
- `PoissonBackoff`：基於泊松分佈生成等待時間。
- `DecorrelatedJitterBackoff`：為分佈式重試提供去相關抖動。
- `ArrayBackoff`：使用調用方定義的等待序列。

## 其他工具

此組件還包含 `retry()` 和 `once()` 輔助函數，以及 `ConfigurationUrlParser`、`Env`、
`HtmlString`、`Number`、`Once`、`Sleep`、`Timebox`、`UuidContainer`、`RedisCommand`、
管道輔助功能和可複用 Trait。在其他組件新增重複工具前，請先檢查
`FriendsOfHyperf\Support` 下已有的類。
