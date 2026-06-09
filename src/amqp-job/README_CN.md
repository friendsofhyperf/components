# Amqp Job

[English](README.md)

## 简介

`friendsofhyperf/amqp-job` 通过 `hyperf/amqp` 分发 PHP 任务对象。任务使用 `AmqpJob`
注解声明 AMQP 绑定，服务启动时，组件会为每个已启用且带有该注解的任务自动注册消费者。

组件默认使用 PHP 序列化，因此只应分发可信的任务对象，并确保消费者可以加载对应的类。

## 安装

```shell
composer require friendsofhyperf/amqp-job
```

Hyperf 会自动发现包中的 `ConfigProvider`。它会注册消费者监听器，并将重试次数存储绑定到
Redis，将消息打包器绑定到 `PhpSerializerPacker`。分发任务前，请先配置 `hyperf/amqp`
及所选的 AMQP 连接池。将 `amqp.enable` 设置为 `false` 会跳过所有任务消费者的自动注册。

## 定义并分发任务

```php
use FriendsOfHyperf\AmqpJob\Annotation\AmqpJob;
use FriendsOfHyperf\AmqpJob\Job;
use FriendsOfHyperf\AmqpJob\JobMessage;
use Hyperf\Amqp\Result;
use Hyperf\Amqp\Producer;

#[AmqpJob(
    exchange: 'hyperf.exchange',
    routingKey: 'hyperf.routing.key',
    pool: 'default',
    queue: 'hyperf.queue',
    maxAttempts: 3,
    confirm: true,
    nums: 2,
)]
class FooJob extends Job
{
    public function __construct(private readonly int $id)
    {
    }

    public function handle(): Result
    {
        // Process $this->id.
        return Result::ACK;
    }
}

function dispatchFoo(Producer $producer, int $id): bool
{
    $message = (new JobMessage(new FooJob($id)))
        ->setExchange('hyperf.exchange')
        ->setRoutingKey('hyperf.routing.key')
        ->setPoolName('default');

    return $producer->produce($message, confirm: true, timeout: 5);
}
```

`dispatch(JobInterface $payload, ?bool $confirm = null, ?int $timeout = null): bool`
是创建并发布 `JobMessage` 的便捷函数。如果任务尚无任务 ID，它会为其分配唯一 ID。可选的
`confirm` 和 `timeout` 参数会覆盖本次分发使用的任务值。

当前 `dispatch()` 不会将任务的交换机、路由键或连接池复制到 `JobMessage`。因此生成的生产者
消息会使用空交换机、空路由键和 Hyperf 的 `default` AMQP 连接池。若要发布到 `AmqpJob`
声明的绑定，请像上例一样创建 `JobMessage`、设置目的地，再交给 `Hyperf\Amqp\Producer`。

## 注解选项

| 选项 | 类型 | 默认值 | 行为 |
| --- | --- | --- | --- |
| `exchange` | `string` | 必填 | 自动消费者使用的交换机，也是 `Job::getExchange()` 的返回值。 |
| `routingKey` | `string` | 必填 | 自动消费者使用的路由键，也是 `Job::getRoutingKey()` 的返回值。 |
| `pool` | `?string` | `null` | 自动消费者连接池，也是 `Job::getPoolName()` 的返回值。 |
| `queue` | `?string` | `null` | 消费者队列；省略时保持未设置。 |
| `maxAttempts` | `int` | `0` | 处理失败的最大尝试次数；`0` 表示不重试。 |
| `maxConsumption` | `int` | `0` | 消费者退出前最多消费的消息数。 |
| `confirm` | `bool` | `false` | `Job::getConfirm()` 的返回值，由 `dispatch()` 使用。 |
| `enable` | `bool` | `true` | 控制是否自动注册消费者。 |
| `nums` | `int` | `1` | 消费者进程数。 |
| `name` | `?string` | `null` | 消费者进程名称。 |

如果任务未使用该注解，其 getter 默认返回交换机 `hyperf`、路由键 `hyperf.job`、无指定
连接池、关闭发布确认、5 秒发布超时和不重试。子类可以提供 `$exchange`、`$routingKey`、
`$poolName`、`$confirm`、`$timeout` 或 `$maxAttempts` 等受保护属性来覆盖这些 getter 值。
如上所述，`dispatch()` 只使用确认和超时 getter。

## 消费与重试

当 `handle()` 没有返回值或返回无法识别的字符串时，`JobConsumer` 会确认任务。返回
`Hyperf\Amqp\Result` 或其字符串值可以直接控制消费结果。

当 `handle()` 抛出异常时，如果存在兼容的日志记录器，消费者会记录异常。设置
`maxAttempts > 0` 后，基于 Redis 的次数计数器会重新入队任务，直到达到配置的总尝试次数。
随后消费者会调用 `fail(Throwable $e)` 并丢弃消息。默认 `fail()` 方法不执行任何操作，
可以覆盖它：

```php
use Throwable;

public function fail(Throwable $e): void
{
    // Report or persist the terminal failure.
}
```

默认 Redis 次数键使用 `hyperf:amqp-job:attempts:` 前缀，并在 86,400 秒后过期，因此启用
重试时必须可用 Redis。绑定 `FriendsOfHyperf\AmqpJob\Contract\Attempt` 或
`FriendsOfHyperf\AmqpJob\Contract\Packer` 可以替换默认实现。记录异常时，消费者会依次
解析 `FriendsOfHyperf\AmqpJob\Contract\LoggerInterface` 和
`Hyperf\Contract\StdoutLoggerInterface`；两者都不可用时会跳过日志记录。

## 注册自定义消费者

通常使用自动注册的消费者即可。若要通过手动声明的消费者消费兼容的序列化任务，请继承
`JobConsumer` 并使用 Hyperf 的 `Consumer` 注解：

```php
namespace App\Amqp\Consumer;

use FriendsOfHyperf\AmqpJob\JobConsumer;
use Hyperf\Amqp\Annotation\Consumer;

#[Consumer(
    exchange: 'hyperf.exchange',
    routingKey: 'hyperf.routing.key',
    queue: 'hyperf.queue',
    name: 'MyConsumer',
    nums: 4,
)]
class MyConsumer extends JobConsumer
{
}
```
