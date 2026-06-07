# Helpers

此组件为 Hyperf 提供常用辅助函数。

## 安装

```shell
composer require friendsofhyperf/helpers
```

## 配置与自动加载

此包通过 Composer 自动加载函数文件。其配置提供器不会注册或发布任何配置，因此无需额外设置。

除 `call` 定义在 `FriendsOfHyperf\Helpers\Command` 命名空间外，其他函数均定义在
`FriendsOfHyperf\Helpers` 命名空间。

## 函数参考

| 函数 | 签名与行为 |
| --- | --- |
| `app` | `app(null\|string\|callable $abstract = null, array $parameters = [])`：从容器解析服务；将可调用值转换为 `Closure`。 |
| `base_path` | `base_path(string $path = ''): string`：返回 `BASE_PATH`，可追加路径。 |
| `blank` / `filled` | 判断值是否为空或非空。模型、数字和布尔值不视为空。 |
| `cache` | `cache(...$arguments)`：无参数时返回缓存服务；字符串参数用于读取；数组参数用于设置其中第一组键值。 |
| `cookie` | 创建 `Cookie`；未传名称时返回 `CookieJarInterface` 服务。非零有效期以分钟为单位。 |
| `class_namespace` | `class_namespace(object\|string $class): string`：返回类的命名空间。 |
| `di` | `di(?string $abstract = null, array $parameters = [])`：解析或创建服务。无容器时直接实例化类；此时不传抽象名称会抛出异常。 |
| `enum_value` | 返回有值枚举的值、纯枚举的名称或原值。空的非字符串值使用可选默认值。 |
| `event` | `event(object $event)`：分发事件并返回分发器的结果。 |
| `fluent` | `fluent(object\|array $value): Fluent`：创建 `Fluent` 对象。 |
| `get_client_ip` | 返回 `x-real-ip` 请求头；不存在时返回请求的 `remote_addr`。 |
| `info` | `info(string\|Stringable $message, array $context = [], bool $backtrace = false)`：写入 info 日志；可附加 `backtrace` 上下文值。 |
| `literal` | 仅有一个位置参数时原样返回；使用命名参数时创建对象。 |
| `logger` | 未传消息时返回默认日志记录器；否则写入 debug 日志，并可附加调用栈。 |
| `logs` | `logs(string $name = 'hyperf', ?string $channel = null): LoggerInterface`：从 `LoggerFactory` 获取日志记录器。 |
| `microseconds` / `milliseconds` / `months` / `weeks` | 创建指定单位的 `CarbonInterval`。 |
| `object_get` | 使用点号读取嵌套对象属性；键为空时返回对象，属性不存在时求值并返回默认值。 |
| `preg_replace_array` | 使用替换数组中的值依次替换每个正则匹配项。 |
| `request` | 未传键时返回请求；支持字符串键、键数组和可选默认值。 |
| `resolve` | `resolve(string\|callable $abstract, array $parameters = [])`：通过 `di` 解析服务，或将可调用值转换为 `Closure`。 |
| `response` | 无参数时返回响应服务；否则使用字符串或 JSON 数组内容和状态码创建响应，并接受响应头数组。 |
| `rescue` | 执行回调；捕获任意 `Throwable` 后返回备用值，可选异常处理器会接收该异常。 |
| `session` | 未传键时返回会话；数组用于存储值，字符串键用于读取值。 |
| `throw_if` / `throw_unless` | 根据条件抛出异常实例、异常类或以消息创建的 `RuntimeException`；不抛出时返回条件值。 |
| `transform` | 仅在值非空时执行回调；否则返回或求值默认值。 |
| `validator` | 无参数时返回验证器工厂；否则使用数据、规则、消息和自定义属性创建验证器。 |
| `when` | 根据求值后的表达式返回选中的值或默认值；选中值为 `Closure` 时执行它。 |
| `Command\call` | `call(string $command, array $arguments = []): int`：使用 `NullOutput` 运行控制台命令并返回退出码。 |

## 可选依赖

仅为应用实际使用的辅助函数或集成安装可选 Hyperf 包。此包建议使用以下兼容的 `~3.2.0`
版本：

| 包 | 相关用途 |
| --- | --- |
| `hyperf/cache` | `cache` |
| `hyperf/di` | 基于容器的服务解析 |
| `hyperf/framework` | 运行时服务绑定和 `Command\call` |
| `hyperf/logger` | `info`、`logger` 和 `logs` |
| `hyperf/session` | `session` |
| `hyperf/validation` | `validator` |
| `hyperf/amqp`、`hyperf/async-queue`、`hyperf/kafka` | 包元数据为相应集成建议的依赖；此组件中的函数未直接引用它们。 |

## 示例

使用命名空间函数前先导入：

```php
use function FriendsOfHyperf\Helpers\blank;
use function FriendsOfHyperf\Helpers\literal;
use function FriendsOfHyperf\Helpers\object_get;
use function FriendsOfHyperf\Helpers\transform;

$profile = literal(name: 'Taylor', contact: (object) ['email' => 'taylor@example.com']);

object_get($profile, 'contact.email'); // taylor@example.com
blank('  '); // true
transform(5, fn (int $value) => $value * 2); // 10
```

`cache` 会根据参数选择行为：

```php
use function FriendsOfHyperf\Helpers\cache;

$cache = cache();
$value = cache('key', 'default');
cache(['key' => 'value'], 60);
```

控制台命令使用单独的命名空间：

```php
use function FriendsOfHyperf\Helpers\Command\call;

$exitCode = call('foo:bar', ['argument' => 'value']);
```
