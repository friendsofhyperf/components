# Facades

Facade 組件為常用的 Hyperf 服務提供靜態代理。

## 安裝

```shell
composer require friendsofhyperf/facade
```

基礎套件依賴 `friendsofhyperf/support`、`hyperf/context` 和 `hyperf/di`。大多數
Facade 還需要可選依賴，請只安裝應用程式實際使用的 Facade 所需套件，例如：

```shell
composer require hyperf/config hyperf/logger
```

## 支援的 Facade

| Facade | 容器存取器 | 額外依賴 |
| --- | --- | --- |
| `AMQP` | `Hyperf\Amqp\Producer` | `hyperf/amqp` |
| `App`, `DI` | `Psr\Container\ContainerInterface` | 已包含（`hyperf/di`） |
| `AsyncQueue` | `Hyperf\AsyncQueue\Driver\DriverFactory` | `hyperf/async-queue` |
| `Cache` | `Psr\SimpleCache\CacheInterface` | `hyperf/cache` |
| `Config` | `Hyperf\Contract\ConfigInterface` | `hyperf/config` |
| `Cookie` | `Hyperf\HttpMessage\Cookie\CookieJarInterface` | `hyperf/framework` |
| `Crypt` | `FriendsOfHyperf\Encryption\Encrypter` | `friendsofhyperf/encryption` |
| `Event` | `Psr\EventDispatcher\EventDispatcherInterface` | `hyperf/framework` |
| `File`, `Filesystem` | `League\Flysystem\Filesystem` | `hyperf/filesystem` |
| `Kafka` | `Hyperf\Kafka\ProducerManager` | `hyperf/kafka` |
| `Log` | `Hyperf\Logger\LoggerFactory` | `hyperf/logger` |
| `Pipeline` | `FriendsOfHyperf\Support\Pipeline\Hub` | 已包含（`friendsofhyperf/support`） |
| `Redis` | `Hyperf\Redis\Redis` | `hyperf/redis` |
| `Request` | `Hyperf\HttpServer\Contract\RequestInterface` | `hyperf/framework` |
| `Response` | `Hyperf\HttpServer\Contract\ResponseInterface` | `hyperf/framework` |
| `Session` | `Hyperf\Contract\SessionInterface` | `hyperf/session` |
| `Translator` | `Hyperf\Contract\TranslatorInterface` | `hyperf/translation` |
| `Validator` | `Hyperf\Validation\Contract\ValidatorFactoryInterface` | `hyperf/validation` |
| `View` | `Hyperf\View\RenderInterface` | `hyperf/view` |

`App` 是 `DI` 的別名，`File` 是 `Filesystem` 的別名。

## 配置與解析

Hyperf 會自動發現此組件，但它的 `ConfigProvider` 不會發佈或合併任何配置。請按各
Hyperf 組件的正常方式配置底層服務。

除下方列出的組件特有方法外，對 Facade 的靜態調用會轉發給
`ApplicationContext` 根據上表存取器解析出的物件。Facade 基類會快取首次解析的
物件。如果容器中不存在對應存取器，將拋出
`Hyperf\Di\Exception\NotFoundException`。

需要底層物件本身時，可以調用 `getFacadeRoot()`：

```php
use FriendsOfHyperf\Facade\Config;

$config = Config::getFacadeRoot();
$name = Config::get('app_name', 'hyperf');
```

每個代理可接受的方法是其底層存取器的公開方法。具體簽名請參考相應組件文檔。

## 組件特有方法

以下公開方法由 Facade 類別自身實作：

| Facade | 方法 | 行為 |
| --- | --- | --- |
| `AMQP` | `dispatch(ProducerMessageInterface $producerMessage): PendingAmqpProducerMessageDispatch` | 建立 AMQP 待調度物件的實例方法。 |
| `AsyncQueue` | `dispatch(Closure\|JobInterface $job): PendingAsyncQueueDispatch` | 建立非同步佇列待調度物件的實例方法。 |
| `AsyncQueue` | `push(JobInterface $job, int $delay = 0, ?string $pool = null)` | 使用指定佇列池推送；當 `$pool` 為 `null` 時使用 `$job->getPoolName()`；文檔返回類型為 `bool`。 |
| `Cookie` | `has($key)` | 檢查目前請求中的 Cookie，而不是 CookieJar；文檔參數類型為 `string`，返回類型為 `bool`。 |
| `Cookie` | `get($key, $default = null)` | 讀取目前請求中的 Cookie；文檔鍵類型為 `string`，返回類型為 `mixed`。 |
| `Kafka` | `dispatch(ProduceMessage $produceMessage): PendingKafkaProducerMessageDispatch` | 建立 Kafka 待調度物件的實例方法。 |
| `Kafka` | `send(ProduceMessage $produceMessage, ?string $pool = null): void` | 以單元素批次發送訊息；未指定池時使用 `default`。 |
| `Kafka` | `sendBatch($produceMessages, ?string $pool = null): void` | 發送文檔類型為 `ProduceMessage[]` 的訊息批次；未指定池時使用 `default`。 |
| `Log` | `channel(string $name = 'hyperf', string $channel = 'default')` | 從 `LoggerFactory` 取得 `LoggerInterface`；其他 `Log` 靜態調用使用這兩個預設值。 |

`AMQP::dispatch`、`AsyncQueue::dispatch` 和 `Kafka::dispatch` 被聲明為實例方法。
它們不是靜態 Facade 代理方法。

```php
use FriendsOfHyperf\Facade\AsyncQueue;
use FriendsOfHyperf\Facade\Cookie;
use FriendsOfHyperf\Facade\Kafka;
use FriendsOfHyperf\Facade\Log;

AsyncQueue::push($job, delay: 5, pool: 'default');

Kafka::send($message);
Kafka::sendBatch([$firstMessage, $secondMessage], 'default');

$token = Cookie::get('token');
$hasToken = Cookie::has('token');

Log::channel('hyperf', 'default')->info('Using an explicit logger channel');
Log::info('Uses the hyperf/default logger');
```
