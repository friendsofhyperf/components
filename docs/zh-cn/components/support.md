# Support

Support 组件提供 FriendsOfHyperf 各组件共享的工具，包括流式派发 API、可序列化闭包任务和
重试退避策略。

## 安装

```shell
composer require friendsofhyperf/support
```

AMQP、Kafka 和异步队列集成为可选功能。派发对应消息类型前，请先安装相应的 Hyperf 组件。

## 流式派发

`dispatch()` 辅助函数会根据传入值的类型选择待派发对象：

- `Closure` 会创建 `CallQueuedClosure` 并使用异步队列派发器。
- `ProducerMessageInterface` 使用 AMQP 生产者派发器。
- `ProduceMessage` 使用 Kafka 生产者派发器。
- 其他值会被拒绝，并抛出 `InvalidArgumentException`。

### 异步队列

```php
use function FriendsOfHyperf\Support\dispatch;

dispatch(function (UserService $users) {
    $users->synchronize();
})
    ->onPool('default')
    ->delay(30)
    ->setMaxAttempts(3);
```

闭包支持依赖注入。待派发对象会在销毁时派发任务。

### AMQP 与 Kafka

```php
dispatch($amqpMessage)
    ->onPool('default')
    ->setExchange('events')
    ->setRoutingKey('user.updated')
    ->setTimeout(5)
    ->setConfirm(true);

dispatch($kafkaMessage)->onPool('default');
```

### 条件配置

所有待派发对象都支持 `when()` 和 `unless()`：

```php
dispatch($job)
    ->when($highPriority, fn ($pending) => $pending->onPool('high-priority'))
    ->unless($canRunNow, fn ($pending) => $pending->delay(60));
```

## 闭包任务

需要将闭包任务传递给其他 API 时，可以直接创建可序列化的异步队列任务：

```php
use FriendsOfHyperf\Support\CallQueuedClosure;

$job = CallQueuedClosure::create(function () {
    return 'completed';
});
$job->setMaxAttempts(3);
```

## 退避策略

退避策略实现会返回下一次等待的毫秒数，并提供 `next()`、`reset()`、`getAttempt()` 和
`sleep()` 方法。

### 数组退避

```php
use FriendsOfHyperf\Support\Backoff\ArrayBackoff;

$custom = new ArrayBackoff([100, 500, 1000, 2000]);
$short = ArrayBackoff::fromPattern('short');
$fromString = ArrayBackoff::fromString('100, 500, 1000');
```

如果希望数组耗尽后返回 `0`，而不是重复最后一个值，请将构造函数第二个参数设为 `false`。

### 可用策略

- `FixedBackoff`：固定等待时间。
- `LinearBackoff`：按固定步长增加等待时间。
- `ExponentialBackoff`：指数增长，可选抖动。
- `FibonacciBackoff`：使用斐波那契数列等待时间。
- `PoissonBackoff`：基于泊松分布生成等待时间。
- `DecorrelatedJitterBackoff`：为分布式重试提供去相关抖动。
- `ArrayBackoff`：使用调用方定义的等待序列。

## 其他工具

此组件还包含 `retry()` 和 `once()` 辅助函数，以及 `ConfigurationUrlParser`、`Env`、
`HtmlString`、`Number`、`Once`、`Sleep`、`Timebox`、`UuidContainer`、`RedisCommand`、
管道辅助功能和可复用 Trait。在其他组件新增重复工具前，请先检查
`FriendsOfHyperf\Support` 下已有的类。
