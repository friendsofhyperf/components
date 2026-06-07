# Amqp Job

## 簡介

`friendsofhyperf/amqp-job` 通過 `hyperf/amqp` 分發 PHP 任務對象。任務使用 `AmqpJob`
註解聲明 AMQP 綁定，服務啟動時，組件會為每個已啟用且帶有該註解的任務自動註冊消費者。

組件默認使用 PHP 序列化，因此只應分發可信的任務對象，並確保消費者可以加載對應的類。

## 安裝

```shell
composer require friendsofhyperf/amqp-job
```

Hyperf 會自動發現包中的 `ConfigProvider`。它會註冊消費者監聽器，並將重試次數存儲綁定到
Redis，將消息打包器綁定到 `PhpSerializerPacker`。分發任務前，請先配置 `hyperf/amqp`
及所選的 AMQP 連接池。將 `amqp.enable` 設置為 `false` 會跳過所有任務消費者的自動註冊。

## 定義並分發任務

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
是創建並發布 `JobMessage` 的便捷函數。如果任務尚無任務 ID，它會為其分配唯一 ID。可選的
`confirm` 和 `timeout` 參數會覆蓋本次分發使用的任務值。

當前 `dispatch()` 不會將任務的交換機、路由鍵或連接池複製到 `JobMessage`。因此生成的生產者
消息會使用空交換機、空路由鍵和 Hyperf 的 `default` AMQP 連接池。若要發布到 `AmqpJob`
聲明的綁定，請像上例一樣創建 `JobMessage`、設置目的地，再交給 `Hyperf\Amqp\Producer`。

## 註解選項

| 選項 | 類型 | 默認值 | 行為 |
| --- | --- | --- | --- |
| `exchange` | `string` | 必填 | 自動消費者使用的交換機，也是 `Job::getExchange()` 的返回值。 |
| `routingKey` | `string` | 必填 | 自動消費者使用的路由鍵，也是 `Job::getRoutingKey()` 的返回值。 |
| `pool` | `?string` | `null` | 自動消費者連接池，也是 `Job::getPoolName()` 的返回值。 |
| `queue` | `?string` | `null` | 消費者隊列；省略時保持未設置。 |
| `maxAttempts` | `int` | `0` | 處理失敗的最大嘗試次數；`0` 表示不重試。 |
| `maxConsumption` | `int` | `0` | 消費者退出前最多消費的消息數。 |
| `confirm` | `bool` | `false` | `Job::getConfirm()` 的返回值，由 `dispatch()` 使用。 |
| `enable` | `bool` | `true` | 控制是否自動註冊消費者。 |
| `nums` | `int` | `1` | 消費者進程數。 |
| `name` | `?string` | `null` | 消費者進程名稱。 |

如果任務未使用該註解，其 getter 默認返回交換機 `hyperf`、路由鍵 `hyperf.job`、無指定
連接池、關閉發布確認、5 秒發布超時和不重試。子類可以提供 `$exchange`、`$routingKey`、
`$poolName`、`$confirm`、`$timeout` 或 `$maxAttempts` 等受保護屬性來覆蓋這些 getter 值。
如上所述，`dispatch()` 只使用確認和超時 getter。

## 消費與重試

當 `handle()` 沒有返回值或返回無法識別的字符串時，`JobConsumer` 會確認任務。返回
`Hyperf\Amqp\Result` 或其字符串值可以直接控制消費結果。

當 `handle()` 拋出異常時，如果存在兼容的日誌記錄器，消費者會記錄異常。設置
`maxAttempts > 0` 後，基於 Redis 的次數計數器會重新入隊任務，直到達到配置的總嘗試次數。
隨後消費者會調用 `fail(Throwable $e)` 並丟棄消息。默認 `fail()` 方法不執行任何操作，
可以覆蓋它：

```php
use Throwable;

public function fail(Throwable $e): void
{
    // Report or persist the terminal failure.
}
```

默認 Redis 次數鍵使用 `hyperf:amqp-job:attempts:` 前綴，並在 86,400 秒後過期，因此啟用
重試時必須可用 Redis。綁定 `FriendsOfHyperf\AmqpJob\Contract\Attempt` 或
`FriendsOfHyperf\AmqpJob\Contract\Packer` 可以替換默認實現。記錄異常時，消費者會依次
解析 `FriendsOfHyperf\AmqpJob\Contract\LoggerInterface` 和
`Hyperf\Contract\StdoutLoggerInterface`；兩者都不可用時會跳過日誌記錄。

## 註冊自定義消費者

通常使用自動註冊的消費者即可。若要通過手動聲明的消費者消費兼容的序列化任務，請繼承
`JobConsumer` 並使用 Hyperf 的 `Consumer` 註解：

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
