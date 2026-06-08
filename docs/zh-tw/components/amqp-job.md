# Amqp Job

## 簡介

`friendsofhyperf/amqp-job` 透過 `hyperf/amqp` 分發 PHP 任務物件。任務使用 `AmqpJob`
註解宣告 AMQP 綁定，服務啟動時，元件會為每個已啟用且帶有該註解的任務自動註冊消費者。

元件預設使用 PHP 序列化，因此只應分發可信的任務物件，並確保消費者可以載入對應的類別。

## 安裝

```shell
composer require friendsofhyperf/amqp-job
```

Hyperf 會自動發現套件中的 `ConfigProvider`。它會註冊消費者監聽器，並將重試次數儲存綁定到
Redis，將訊息封裝器綁定到 `PhpSerializerPacker`。分發任務前，請先設定 `hyperf/amqp`
及所選的 AMQP 連線池。將 `amqp.enable` 設定為 `false` 會略過所有任務消費者的自動註冊。

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
是建立並發布 `JobMessage` 的便捷函式。如果任務尚無任務 ID，它會為其分配唯一 ID。可選的
`confirm` 和 `timeout` 參數會覆寫本次分發使用的任務值。

目前 `dispatch()` 不會將任務的交換器、路由鍵或連線池複製到 `JobMessage`。因此產生的生產者
訊息會使用空交換器、空路由鍵和 Hyperf 的 `default` AMQP 連線池。若要發布到 `AmqpJob`
宣告的綁定，請像上例一樣建立 `JobMessage`、設定目的地，再交給 `Hyperf\Amqp\Producer`。

## 註解選項

| 選項 | 類型 | 預設值 | 行為 |
| --- | --- | --- | --- |
| `exchange` | `string` | 必填 | 自動消費者使用的交換器，也是 `Job::getExchange()` 的回傳值。 |
| `routingKey` | `string` | 必填 | 自動消費者使用的路由鍵，也是 `Job::getRoutingKey()` 的回傳值。 |
| `pool` | `?string` | `null` | 自動消費者連線池，也是 `Job::getPoolName()` 的回傳值。 |
| `queue` | `?string` | `null` | 消費者佇列；省略時保持未設定。 |
| `maxAttempts` | `int` | `0` | 處理失敗的最大嘗試次數；`0` 表示不重試。 |
| `maxConsumption` | `int` | `0` | 消費者退出前最多消費的訊息數。 |
| `confirm` | `bool` | `false` | `Job::getConfirm()` 的回傳值，由 `dispatch()` 使用。 |
| `enable` | `bool` | `true` | 控制是否自動註冊消費者。 |
| `nums` | `int` | `1` | 消費者程序數。 |
| `name` | `?string` | `null` | 消費者程序名稱。 |

如果任務未使用該註解，其 getter 預設回傳交換器 `hyperf`、路由鍵 `hyperf.job`、無指定
連線池、關閉發布確認、5 秒發布逾時和不重試。子類別可以提供 `$exchange`、`$routingKey`、
`$poolName`、`$confirm`、`$timeout` 或 `$maxAttempts` 等受保護屬性來覆寫這些 getter 值。
如上所述，`dispatch()` 只使用確認和逾時 getter。

## 消費與重試

當 `handle()` 沒有回傳值或回傳無法識別的字串時，`JobConsumer` 會確認任務。回傳
`Hyperf\Amqp\Result` 或其字串值可以直接控制消費結果。

當 `handle()` 擲回例外時，如果存在相容的日誌記錄器，消費者會記錄例外。設定
`maxAttempts > 0` 後，基於 Redis 的次數計數器會重新入列任務，直到達到設定的總嘗試次數。
隨後消費者會呼叫 `fail(Throwable $e)` 並丟棄訊息。預設 `fail()` 方法不執行任何操作，
可以覆寫它：

```php
use Throwable;

public function fail(Throwable $e): void
{
    // Report or persist the terminal failure.
}
```

預設 Redis 次數鍵使用 `hyperf:amqp-job:attempts:` 前綴，並在 86,400 秒後過期，因此啟用
重試時必須可用 Redis。綁定 `FriendsOfHyperf\AmqpJob\Contract\Attempt` 或
`FriendsOfHyperf\AmqpJob\Contract\Packer` 可以取代預設實作。記錄例外時，消費者會依序
解析 `FriendsOfHyperf\AmqpJob\Contract\LoggerInterface` 和
`Hyperf\Contract\StdoutLoggerInterface`；兩者都不可用時會略過日誌記錄。

## 註冊自訂消費者

通常使用自動註冊的消費者即可。若要透過手動宣告的消費者消費相容的序列化任務，請繼承
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
