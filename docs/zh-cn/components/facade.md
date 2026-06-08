# Facades

Facade 组件为常用的 Hyperf 服务提供静态代理。

## 安装

```shell
composer require friendsofhyperf/facade
```

基础包依赖 `friendsofhyperf/support`、`hyperf/context` 和 `hyperf/di`。大多数
Facade 还需要可选依赖，请仅安装应用实际使用的 Facade 所需的包，例如：

```shell
composer require hyperf/config hyperf/logger
```

## 支持的 Facade

| Facade | 容器访问器 | 额外依赖 |
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

`App` 是 `DI` 的别名，`File` 是 `Filesystem` 的别名。

## 配置与解析

Hyperf 会自动发现此组件，但它的 `ConfigProvider` 不发布或合并任何配置。请按各
Hyperf 组件的正常方式配置底层服务。

除下方列出的组件特有方法外，对 Facade 的静态调用会转发给
`ApplicationContext` 根据上表访问器解析出的对象。Facade 基类会缓存首次解析的
对象。如果容器中不存在对应访问器，将抛出
`Hyperf\Di\Exception\NotFoundException`。

需要底层对象本身时，可以调用 `getFacadeRoot()`：

```php
use FriendsOfHyperf\Facade\Config;

$config = Config::getFacadeRoot();
$name = Config::get('app_name', 'hyperf');
```

每个代理可接受的方法是其底层访问器的公开方法。具体签名请参考相应组件文档。

## 组件特有方法

以下公开方法由 Facade 类自身实现：

| Facade | 方法 | 行为 |
| --- | --- | --- |
| `AMQP` | `dispatch(ProducerMessageInterface $producerMessage): PendingAmqpProducerMessageDispatch` | 创建 AMQP 待调度对象的实例方法。 |
| `AsyncQueue` | `dispatch(Closure\|JobInterface $job): PendingAsyncQueueDispatch` | 创建异步队列待调度对象的实例方法。 |
| `AsyncQueue` | `push(JobInterface $job, int $delay = 0, ?string $pool = null)` | 使用指定队列池推送；当 `$pool` 为 `null` 时使用 `$job->getPoolName()`；文档返回类型为 `bool`。 |
| `Cookie` | `has($key)` | 检查当前请求中的 Cookie，而不是 CookieJar；文档参数类型为 `string`，返回类型为 `bool`。 |
| `Cookie` | `get($key, $default = null)` | 读取当前请求中的 Cookie；文档键类型为 `string`，返回类型为 `mixed`。 |
| `Kafka` | `dispatch(ProduceMessage $produceMessage): PendingKafkaProducerMessageDispatch` | 创建 Kafka 待调度对象的实例方法。 |
| `Kafka` | `send(ProduceMessage $produceMessage, ?string $pool = null): void` | 以单元素批次发送消息；未指定队列池时使用 `default`。 |
| `Kafka` | `sendBatch($produceMessages, ?string $pool = null): void` | 发送文档类型为 `ProduceMessage[]` 的消息批次；未指定队列池时使用 `default`。 |
| `Log` | `channel(string $name = 'hyperf', string $channel = 'default')` | 从 `LoggerFactory` 获取 `LoggerInterface`；其他 `Log` 静态调用使用这两个默认值。 |

`AMQP::dispatch`、`AsyncQueue::dispatch` 和 `Kafka::dispatch` 被声明为实例方法。
它们不是静态 Facade 代理方法。

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
