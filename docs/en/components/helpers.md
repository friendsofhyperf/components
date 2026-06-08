# Helpers

This component provides commonly used helper functions for Hyperf.

## Installation

```shell
composer require friendsofhyperf/helpers
```

## Configuration and Autoloading

The package autoloads its function files through Composer. Its configuration provider does not
register or publish any configuration, so no additional setup is required.

Functions are defined in the `FriendsOfHyperf\Helpers` namespace, except for `call`, which is
defined in `FriendsOfHyperf\Helpers\Command`.

## Function Reference

| Function | Signature and behavior |
| --- | --- |
| `app` | `app(null\|string\|callable $abstract = null, array $parameters = [])`: resolves from the container; converts a callable to a `Closure`. |
| `base_path` | `base_path(string $path = ''): string`: returns `BASE_PATH`, optionally with a path appended. |
| `blank` / `filled` | Determine whether a value is blank or not blank. Models, numbers, and booleans are not blank. |
| `cache` | `cache(...$arguments)`: returns the cache service with no arguments, gets a string key, or sets the first key/value pair from an array. |
| `cookie` | Creates a `Cookie`; with no name, returns the `CookieJarInterface` service. A non-zero lifetime is specified in minutes. |
| `class_namespace` | `class_namespace(object\|string $class): string`: returns the class namespace. |
| `di` | `di(?string $abstract = null, array $parameters = [])`: resolves or makes a service. Without a container, it instantiates the class directly; calling it without an abstract then throws. |
| `enum_value` | Returns a backed enum's value, a unit enum's name, or the original value. Blank non-string values use the optional default. |
| `event` | `event(object $event)`: dispatches an event and returns the dispatcher's result. |
| `fluent` | `fluent(object\|array $value): Fluent`: creates a `Fluent` object. |
| `get_client_ip` | Returns the `x-real-ip` header, falling back to the request's `remote_addr`. |
| `info` | `info(string\|Stringable $message, array $context = [], bool $backtrace = false)`: writes an info log; optionally adds a `backtrace` context value. |
| `literal` | Returns the sole positional argument unchanged, or creates an object from named arguments. |
| `logger` | With no message, returns the default logger; otherwise writes a debug log and can include a backtrace. |
| `logs` | `logs(string $name = 'hyperf', ?string $channel = null): LoggerInterface`: gets a logger from `LoggerFactory`. |
| `microseconds` / `milliseconds` / `months` / `weeks` | Create a `CarbonInterval` for the given unit. |
| `object_get` | Gets a nested object property with dot notation; returns the object for an empty key and evaluates the default when missing. |
| `preg_replace_array` | Replaces each regex match sequentially with values from the replacements array. |
| `request` | With no key, returns the request; accepts a string key or an array of keys and an optional default. |
| `resolve` | `resolve(string\|callable $abstract, array $parameters = [])`: resolves from `di`, or converts a callable to a `Closure`. |
| `response` | With no arguments, returns the response service; otherwise creates a response with string or JSON-array content and a status code, and accepts a headers array. |
| `rescue` | Runs a callback and returns a fallback after any `Throwable`; an optional exception handler receives the throwable. |
| `session` | With no key, returns the session; an array stores values, and a string key retrieves a value. |
| `throw_if` / `throw_unless` | Throw an exception instance, exception class, or `RuntimeException` message according to the condition; otherwise return the condition. |
| `transform` | Runs the callback only for a filled value; otherwise returns or evaluates the default. |
| `validator` | With no arguments, returns the validator factory; otherwise creates a validator from data, rules, messages, and custom attributes. |
| `when` | Returns the selected value or default according to the evaluated expression; evaluates the selected value when it is a `Closure`. |
| `Command\call` | `call(string $command, array $arguments = []): int`: runs a console command with `NullOutput` and returns its exit code. |

## Optional Dependencies

Install optional Hyperf packages only for the helpers or integrations your application uses. The
package suggests compatible `~3.2.0` versions of:

| Package | Relevant usage |
| --- | --- |
| `hyperf/cache` | `cache` |
| `hyperf/di` | Container-backed resolution |
| `hyperf/framework` | Runtime service bindings and `Command\call` |
| `hyperf/logger` | `info`, `logger`, and `logs` |
| `hyperf/session` | `session` |
| `hyperf/validation` | `validator` |
| `hyperf/amqp`, `hyperf/async-queue`, `hyperf/kafka` | Suggested by the package metadata for their corresponding integrations; no function in this component directly references them. |

## Examples

Import namespaced functions before using them:

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

The `cache` helper selects its behavior from its arguments:

```php
use function FriendsOfHyperf\Helpers\cache;

$cache = cache();
$value = cache('key', 'default');
cache(['key' => 'value'], 60);
```

Console commands use a separate namespace:

```php
use function FriendsOfHyperf\Helpers\Command\call;

$exitCode = call('foo:bar', ['argument' => 'value']);
```
