# Macros

该组件为 Hyperf 的集合、上下文、请求和字符串类添加常用宏。组件的 `ConfigProvider` 会在应用启动时
自动注册 mixin，无需发布配置文件。

## 安装

```shell
composer require friendsofhyperf/macros
```

## 可选依赖

已注册的宏会直接使用以下可选包：

- `hyperf/http-server`：所有 `Request` 宏。
- `league/commonmark`：`Str::markdown`、`Str::inlineMarkdown` 及对应的 `Stringable` 方法。
- `voku/portable-ascii`：`Str::transliterate`。
- `friendsofhyperf/encryption`：`Stringable::encrypt` 和 `Stringable::decrypt`；同时需要配置
  encryption 组件。

`composer.json` 还建议安装用于生成 UUID 的 `ramsey/uuid`、用于生成 ULID 的 `symfony/uid` 和
`opis/closure`。当前 mixin 源码不会直接调用这三个包。

## 支持方法

### Hyperf\Collection\Arr

- `Arr::arrayable($value)`
- `Arr::array(ArrayAccess|array $array, null|string|int $key, ?array $default = null)`
- `Arr::boolean(ArrayAccess|array $array, null|string|int $key, ?bool $default = null)`
- `Arr::every($array, callable $callback)`
- `Arr::float(ArrayAccess|array $array, null|string|int $key, ?float $default = null)`
- `Arr::from($items)`
- `Arr::hasAll($array, $keys)`
- `Arr::integer(ArrayAccess|array $array, null|string|int $key, ?int $default = null)`
- `Arr::some($array, callable $callback)`
- `Arr::sortByMany($array, $comparisons = [])`
- `Arr::string(ArrayAccess|array $array, null|string|int $key, ?string $default = null)`

带类型的读取方法支持点号路径；解析出的值不符合目标类型时会抛出 `InvalidArgumentException`。
`Arr::from` 可将受支持的数组、Enumerable/Arrayable 对象、可遍历对象、支持 JSON 的对象和普通对象
转换为数组，但拒绝标量值。

### Hyperf\Collection\Collection

- `Collection::collapseWithKeys()`

### Hyperf\Collection\LazyCollection

- `LazyCollection::collapseWithKeys()`

`collapseWithKeys` 在保留键的同时展平嵌套数组或集合。非数组和非集合值会被忽略，后出现的重复键会
覆盖先前的值。

### Hyperf\Context\Context

- `Context::decrement(string $id, int $step = 1, ?int $coroutineId = null)`
- `Context::increment(string $id, int $step = 1, ?int $coroutineId = null)`

两个方法都通过 `Context::override` 更新选定上下文中的值。缺失的值会先按零处理，再应用步长。

### Hyperf\HttpServer\Request

- `Request::allFiles()`
- `Request::anyFilled($keys)`
- `Request::boolean(string $key = '', $default = false)`
- `Request::collect($key = null)`
- `Request::date(string $key, $format = null, $tz = null)`
- `Request::enum($key, $enumClass)`
- `Request::except($keys)`
- `Request::exists($key)`
- `Request::fake($closure = null)`
- `Request::filled($key)`
- `Request::float($key, $default = null)`
- `Request::fluent($key = null)`
- `Request::getHost()`
- `Request::getHttpHost()`
- `Request::getPort()`
- `Request::getPsrRequest()`
- `Request::getScheme()`
- `Request::getSchemeAndHttpHost()`
- `Request::hasAny($keys)`
- `Request::host()`
- `Request::httpHost()`
- `Request::integer($key, $default = null)`
- `Request::isEmptyString($key)`
- `Request::isJson()`
- `Request::isNotFilled($key)`
- `Request::isSecure()`
- `Request::keys()`
- `Request::merge(array $input)`
- `Request::mergeIfMissing(array $input)`
- `Request::missing($key)`
- `Request::only($keys)`
- `Request::schemeAndHttpHost()`
- `Request::str($key, $default = null)`
- `Request::string($key, $default = null)`
- `Request::validate(array $rules, ...$params)`
- `Request::validateWithBag($errorBag, $rules, ...$params)`
- `Request::wantsJson()`
- `Request::whenFilled($key, callable $callback, ?callable $default = null)`
- `Request::whenHas($key, callable $callback, ?callable $default = null)`

`Request::fake` 创建独立的 PSR-7 `ServerRequest`，并可选择将其传给回调。`merge` 和
`mergeIfMissing` 会更新当前上下文中存储的解析后输入。`validate` 和 `validateWithBag` 会从容器
解析 Hyperf 的 `ValidatorFactoryInterface`。

### Hyperf\Stringable\Str

- `Str::createUuidsNormally()`
- `Str::createUuidsUsing(?callable $factory = null)`
- `Str::deduplicate(string $string, string $character = ' ')`
- `Str::doesntEndWith($haystack, $needles)`
- `Str::doesntStartWith($haystack, $needles)`
- `Str::inlineMarkdown($string, array $options = [])`
- `Str::markdown($string, array $options = [], array $extensions = [])`
- `Str::transliterate($string, $unknown = '?', $strict = false)`

### Hyperf\Stringable\Stringable

- `Stringable::decrypt(bool $serialize = false)`
- `Stringable::deduplicate(string $character = ' ')`
- `Stringable::doesntEndWith($needles)`
- `Stringable::doesntStartWith($needles)`
- `Stringable::encrypt(bool $serialize = false)`
- `Stringable::hash(string $algorithm)`
- `Stringable::inlineMarkdown(array $options = [])`
- `Stringable::markdown(array $options = [], array $extensions = [])`
- `Stringable::toHtmlString()`
- `Stringable::whenIsAscii($callback, $default = null)`

多数 `Stringable` 转换宏返回新的 `Stringable` 实例，因此可以链式调用。两个 `doesnt*` 方法返回
布尔值，`toHtmlString` 返回 `FriendsOfHyperf\Support\HtmlString`。

## 示例

```php
use Hyperf\Collection\Arr;
use Hyperf\Context\Context;
use Hyperf\Stringable\Str;

$users = Arr::sortByMany($users, ['name', ['age', false]]);

Context::increment('processed');

$slug = Str::deduplicate('docs///macros', '/');
```
