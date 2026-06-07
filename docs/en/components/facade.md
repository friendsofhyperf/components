# Facades

The facade component provides static proxies for commonly used Hyperf services.

## Installation

```shell
composer require friendsofhyperf/facade
```

The base package requires `friendsofhyperf/support`, `hyperf/context`, and `hyperf/di`.
Most facades need an optional package. Install only the packages required by the
facades that your application uses, for example:

```shell
composer require hyperf/config hyperf/logger
```

## Supported Facades

| Facade | Container accessor | Additional package |
| --- | --- | --- |
| `AMQP` | `Hyperf\Amqp\Producer` | `hyperf/amqp` |
| `App`, `DI` | `Psr\Container\ContainerInterface` | Included (`hyperf/di`) |
| `AsyncQueue` | `Hyperf\AsyncQueue\Driver\DriverFactory` | `hyperf/async-queue` |
| `Cache` | `Psr\SimpleCache\CacheInterface` | `hyperf/cache` |
| `Config` | `Hyperf\Contract\ConfigInterface` | `hyperf/config` |
| `Cookie` | `Hyperf\HttpMessage\Cookie\CookieJarInterface` | `hyperf/framework` |
| `Crypt` | `FriendsOfHyperf\Encryption\Encrypter` | `friendsofhyperf/encryption` |
| `Event` | `Psr\EventDispatcher\EventDispatcherInterface` | `hyperf/framework` |
| `File`, `Filesystem` | `League\Flysystem\Filesystem` | `hyperf/filesystem` |
| `Kafka` | `Hyperf\Kafka\ProducerManager` | `hyperf/kafka` |
| `Log` | `Hyperf\Logger\LoggerFactory` | `hyperf/logger` |
| `Pipeline` | `FriendsOfHyperf\Support\Pipeline\Hub` | Included (`friendsofhyperf/support`) |
| `Redis` | `Hyperf\Redis\Redis` | `hyperf/redis` |
| `Request` | `Hyperf\HttpServer\Contract\RequestInterface` | `hyperf/framework` |
| `Response` | `Hyperf\HttpServer\Contract\ResponseInterface` | `hyperf/framework` |
| `Session` | `Hyperf\Contract\SessionInterface` | `hyperf/session` |
| `Translator` | `Hyperf\Contract\TranslatorInterface` | `hyperf/translation` |
| `Validator` | `Hyperf\Validation\Contract\ValidatorFactoryInterface` | `hyperf/validation` |
| `View` | `Hyperf\View\RenderInterface` | `hyperf/view` |

`App` is an alias of `DI`, and `File` is an alias of `Filesystem`.

## Configuration And Resolution

The package is discovered by Hyperf, but its `ConfigProvider` does not publish or
merge any configuration. Configure each underlying Hyperf component normally.

Except for the component-specific methods below, a static facade call is forwarded
to the object resolved from `ApplicationContext` by the accessor shown above. The
first resolved object is cached by the facade base class. If the container does not
contain the accessor, a `Hyperf\Di\Exception\NotFoundException` is thrown.

Use `getFacadeRoot()` when the underlying object itself is required:

```php
use FriendsOfHyperf\Facade\Config;

$config = Config::getFacadeRoot();
$name = Config::get('app_name', 'hyperf');
```

The methods accepted by each proxy are the public methods of its underlying
accessor. Refer to the corresponding component documentation for their signatures.

## Component-Specific Methods

These public methods are implemented by the facade classes themselves:

| Facade | Method | Behavior |
| --- | --- | --- |
| `AMQP` | `dispatch(ProducerMessageInterface $producerMessage): PendingAmqpProducerMessageDispatch` | Instance method that creates a pending AMQP dispatch. |
| `AsyncQueue` | `dispatch(Closure\|JobInterface $job): PendingAsyncQueueDispatch` | Instance method that creates a pending async-queue dispatch. |
| `AsyncQueue` | `push(JobInterface $job, int $delay = 0, ?string $pool = null)` | Pushes through the selected pool, or through `$job->getPoolName()` when the pool is `null`; documented to return `bool`. |
| `Cookie` | `has($key)` | Checks the current request cookie, not the cookie jar; documented to accept `string` and return `bool`. |
| `Cookie` | `get($key, $default = null)` | Reads the current request cookie; documented to accept a `string` key and return `mixed`. |
| `Kafka` | `dispatch(ProduceMessage $produceMessage): PendingKafkaProducerMessageDispatch` | Instance method that creates a pending Kafka dispatch. |
| `Kafka` | `send(ProduceMessage $produceMessage, ?string $pool = null): void` | Sends one message as a batch through the selected pool, defaulting to `default`. |
| `Kafka` | `sendBatch($produceMessages, ?string $pool = null): void` | Sends a documented `ProduceMessage[]` batch through the selected pool, defaulting to `default`. |
| `Log` | `channel(string $name = 'hyperf', string $channel = 'default')` | Gets a `LoggerInterface` from `LoggerFactory`. Other static `Log` calls use these defaults. |

`AMQP::dispatch`, `AsyncQueue::dispatch`, and `Kafka::dispatch` are declared as
instance methods, not static facade proxy methods.

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
