# Macros

This component adds commonly used macros to Hyperf collection, context, request, and string classes.
Its `ConfigProvider` registers the mixins automatically when the application boots; no configuration
file needs to be published.

## Installation

```shell
composer require friendsofhyperf/macros
```

## Optional Dependencies

The registered macros directly use these optional packages:

- `hyperf/http-server`: all `Request` macros.
- `league/commonmark`: `Str::markdown`, `Str::inlineMarkdown`, and their `Stringable` equivalents.
- `voku/portable-ascii`: `Str::transliterate`.
- `friendsofhyperf/encryption`: `Stringable::encrypt` and `Stringable::decrypt`; the encryption
  component must also be configured.

`composer.json` also suggests `ramsey/uuid` for UUID generation, `symfony/uid` for ULID generation,
and `opis/closure`. The current mixin source does not call these three packages directly.

## Supported Methods

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

The typed getters use dot notation and throw `InvalidArgumentException` when the resolved value does
not have the requested type. `Arr::from` converts supported arrays, enumerable/arrayable objects,
traversables, JSON-capable objects, and ordinary objects to arrays; scalar values are rejected.

### Hyperf\Collection\Collection

- `Collection::collapseWithKeys()`

### Hyperf\Collection\LazyCollection

- `LazyCollection::collapseWithKeys()`

`collapseWithKeys` flattens nested arrays or collections while preserving their keys. Non-array and
non-collection values are ignored, and later duplicate keys replace earlier values.

### Hyperf\Context\Context

- `Context::decrement(string $id, int $step = 1, ?int $coroutineId = null)`
- `Context::increment(string $id, int $step = 1, ?int $coroutineId = null)`

Both methods update the selected context value through `Context::override`. A missing value starts at
zero before the step is applied.

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

`Request::fake` creates a standalone PSR-7 `ServerRequest` and optionally passes it through a
callback. `merge` and `mergeIfMissing` update the parsed input stored in the current context.
`validate` and `validateWithBag` resolve Hyperf's `ValidatorFactoryInterface` from the container.

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

Most `Stringable` transformation macros return a new `Stringable` instance, so they can be chained.
The two `doesnt*` methods return booleans, and `toHtmlString` returns
`FriendsOfHyperf\Support\HtmlString`.

## Examples

```php
use Hyperf\Collection\Arr;
use Hyperf\Context\Context;
use Hyperf\Stringable\Str;

$users = Arr::sortByMany($users, ['name', ['age', false]]);

Context::increment('processed');

$slug = Str::deduplicate('docs///macros', '/');
```
