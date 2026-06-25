# Macros

該元件為 Hyperf 的集合、上下文、請求和字串類新增常用宏。元件的 `ConfigProvider` 會在應用啟動時
自動註冊 mixin，無需釋出配置檔案。

## 安裝

```shell
composer require friendsofhyperf/macros
```

## 可選依賴

已註冊的宏會直接使用以下可選包：

- `hyperf/http-server`：所有 `Request` 宏。
- `league/commonmark`：`Str::markdown`、`Str::inlineMarkdown` 及對應的 `Stringable` 方法。
- `voku/portable-ascii`：`Str::transliterate`。
- `friendsofhyperf/encryption`：`Stringable::encrypt` 和 `Stringable::decrypt`；同時需要配置
  encryption 元件。

`composer.json` 還建議安裝用於生成 UUID 的 `ramsey/uuid`、用於生成 ULID 的 `symfony/uid` 和
`opis/closure`。當前 mixin 原始碼不會直接呼叫這三個包。

## 支援方法

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

帶型別的讀取方法支援點號路徑；解析出的值不符合目標型別時會丟擲 `InvalidArgumentException`。
`Arr::from` 可將受支援的陣列、Enumerable/Arrayable 物件、可遍歷物件、支援 JSON 的物件和普通物件
轉換為陣列，但拒絕標量值。

### Hyperf\Collection\Collection

- `Collection::collapseWithKeys()`

### Hyperf\Collection\LazyCollection

- `LazyCollection::collapseWithKeys()`

`collapseWithKeys` 在保留鍵的同時展平巢狀陣列或集合。非陣列和非集合值會被忽略，後出現的重複鍵會
覆蓋先前的值。

### Hyperf\Context\Context

- `Context::decrement(string $id, int $step = 1, ?int $coroutineId = null)`
- `Context::increment(string $id, int $step = 1, ?int $coroutineId = null)`

兩個方法都透過 `Context::override` 更新選定上下文中的值。缺失的值會先按零處理，再應用步長。

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

`Request::fake` 建立獨立的 PSR-7 `ServerRequest`，並可選擇將其傳給回撥。`merge` 和
`mergeIfMissing` 會更新當前上下文中儲存的解析後輸入。`validate` 和 `validateWithBag` 會從容器
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

多數 `Stringable` 轉換宏返回新的 `Stringable` 例項，因此可以鏈式呼叫。兩個 `doesnt*` 方法返回
布林值，`toHtmlString` 返回 `FriendsOfHyperf\Support\HtmlString`。

## 示例

```php
use Hyperf\Collection\Arr;
use Hyperf\Context\Context;
use Hyperf\Stringable\Str;

$users = Arr::sortByMany($users, ['name', ['age', false]]);

Context::increment('processed');

$slug = Str::deduplicate('docs///macros', '/');
```
