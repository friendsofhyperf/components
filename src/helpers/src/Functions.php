<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Helpers;

use BackedEnum;
use Carbon\CarbonInterval;
use Closure;
use Countable;
use Exception;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\SessionInterface;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\Database\Model\Model;
use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\HttpMessage\Cookie\CookieJarInterface;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Stringable\Str;
use Hyperf\Support\Fluent;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use RuntimeException;
use stdClass;
use Stringable;
use Throwable;
use UnitEnum;

use function Hyperf\Collection\value;
use function Hyperf\Tappable\tap;

/**
 * Get the available container instance.
 *
 * @template TClass
 *
 * @param callable|class-string<TClass>|string $abstract
 *
 * @return ($abstract is callable ? Closure : ($abstract is class-string<TClass> ? TClass : mixed))
 */
function app(null|string|callable $abstract = null, array $parameters = [])
{
    if (is_callable($abstract)) {
        return Closure::fromCallable($abstract);
    }

    return di($abstract, $parameters);
}

/**
 * Get the path to the base of the install.
 */
function base_path(string $path = ''): string
{
    return BASE_PATH . ($path ? '/' . $path : $path);
}

/**
 * Determine if the given value is "blank".
 *
 * @param mixed $value
 */
function blank($value): bool
{
    return match (true) {
        is_null($value) => true,
        is_string($value) => trim($value) === '',
        is_numeric($value) || is_bool($value) => false,
        $value instanceof Model => false,
        $value instanceof Countable => count($value) === 0,
        $value instanceof Stringable => trim((string) $value) === '',
        default => empty($value)
    };
}

/**
 * Get / set the specified cache value.
 *
 * If an array is passed, we'll assume you want to put to the cache.
 *
 * @return ($arguments is empty ? CacheInterface : mixed)
 * @throws Exception
 */
function cache(...$arguments)
{
    $cache = di(CacheInterface::class);

    if (empty($arguments)) {
        return $cache;
    }

    if (is_string($arguments[0])) {
        return $cache->get(...$arguments);
    }

    if (! is_array($arguments[0])) {
        throw new Exception(
            'When setting a value in the cache, you must pass an array of key / value pairs.'
        );
    }

    return $cache->set(key($arguments[0]), reset($arguments[0]), $arguments[1] ?? null);
}

/**
 * Create a new cookie instance.
 *
 * @return ($name is null ? CookieJarInterface : Cookie)
 */
function cookie(?string $name = null, ?string $value = null, int $minutes = 0, ?string $path = null, ?string $domain = null, bool $secure = false, bool $httpOnly = true, bool $raw = false, ?string $sameSite = null)
{
    if (is_null($name)) {
        return di(CookieJarInterface::class);
    }

    $time = ($minutes == 0) ? 0 : $minutes * 60;

    return new Cookie($name, $value, $time, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
}

/**
 * Get the class "namespace" of the given object / class.
 *
 * @param object|string $class
 */
function class_namespace($class): string
{
    $class = is_object($class) ? get_class($class) : $class;

    return Str::classNamespace($class);
}

/**
 * Get the available container instance.
 *
 * @template TClass
 *
 * @param null|class-string<TClass>|string $abstract
 *
 * @return ($abstract is null ? ContainerInterface : ($abstract is class-string<TClass> ? TClass : mixed))
 */
function di(?string $abstract = null, array $parameters = [])
{
    if (ApplicationContext::hasContainer()) {
        /** @var \Hyperf\Contract\ContainerInterface $container */
        $container = ApplicationContext::getContainer();

        if (is_null($abstract)) {
            return $container;
        }

        if (count($parameters) == 0 && $container->has($abstract)) {
            return $container->get($abstract);
        }

        return $container->make($abstract, $parameters);
    }

    if (is_null($abstract)) {
        throw new InvalidArgumentException('Invalid argument $abstract');
    }

    return new $abstract(...array_values($parameters));
}

/**
 * Return a scalar value for the given value that might be an enum.
 *
 * @internal
 *
 * @template TValue
 * @template TDefault
 *
 * @param TValue $value
 * @param TDefault|(callable(TValue): TDefault) $default
 * @return ($value is empty ? TDefault : mixed)
 */
function enum_value($value, $default = null)
{
    if (is_string($value) && empty($value)) {
        return $value;
    }

    return transform($value, fn ($value) => match (true) {
        $value instanceof BackedEnum => $value->value,
        $value instanceof UnitEnum => $value->name,

        default => $value,
    }, $default);
}

/**
 * Dispatch an event and call the listeners.
 *
 * @template TValue of object
 *
 * @param TValue $event
 *
 * @return TValue
 */
function event(object $event)
{
    return di(EventDispatcherInterface::class)->dispatch($event);
}

/**
 * Determine if a value is "filled".
 *
 * @param mixed $value
 */
function filled($value): bool
{
    return ! blank($value);
}

/**
 * Create an Fluent object from the given value.
 *
 * @param object|array $value
 */
function fluent($value): Fluent
{
    return new Fluent($value);
}

/**
 * Get client IP.
 */
function get_client_ip(): string
{
    /** @var RequestInterface $request */
    $request = di(RequestInterface::class);
    return $request->getHeaderLine('x-real-ip') ?: $request->server('remote_addr');
}

/**
 * @param string|Stringable $message
 */
function info($message, array $context = [], bool $backtrace = false)
{
    if ($backtrace) {
        $traces = debug_backtrace();
        $context['backtrace'] = sprintf('%s:%s', $traces[0]['file'], $traces[0]['line']);
    }

    logs()->info($message, $context);
}

/**
 * Return a new literal or anonymous object using named arguments.
 *
 * @return stdClass
 */
function literal(...$arguments)
{
    if (count($arguments) === 1 && array_is_list($arguments)) {
        return $arguments[0];
    }

    return (object) $arguments;
}

/**
 * @param null|string|Stringable $message
 * @return ($message is null ? LoggerInterface : mixed)
 */
function logger($message = null, array $context = [], bool $backtrace = false)
{
    if (is_null($message)) {
        return logs();
    }

    if ($backtrace) {
        $traces = debug_backtrace();
        $context['backtrace'] = array_map(fn ($trace) => sprintf('%s:%s', $trace['file'], $trace['line']), $traces);
    }

    logs()->debug($message, $context);
}

function logs(string $name = 'hyperf', ?string $channel = null): LoggerInterface
{
    return di(LoggerFactory::class)->get($name, $channel);
}

/**
 * Get the current date / time plus the given number of microseconds.
 */
function microseconds(int $microseconds): CarbonInterval
{
    return CarbonInterval::microseconds($microseconds);
}

/**
 * Get the current date / time plus the given number of months.
 */
function months(int $months): CarbonInterval
{
    return CarbonInterval::months($months);
}

/**
 * Get the current date / time plus the given number of milliseconds.
 */
function milliseconds(int $milliseconds): CarbonInterval
{
    return CarbonInterval::milliseconds($milliseconds);
}

/**
 * Get an item from an object using "dot" notation.
 *
 * @template TValue of object
 *
 * @param TValue $object
 * @param null|string $key
 * @param mixed $default
 * @return ($key is empty ? TValue : mixed)
 */
function object_get($object, $key = '', $default = null)
{
    if (is_null($key) || trim($key) == '') {
        return $object;
    }

    foreach (explode('.', $key) as $segment) {
        if (! is_object($object) || ! isset($object->{$segment})) {
            return value($default);
        }

        $object = $object->{$segment};
    }

    return $object;
}

/**
 * Replace a given pattern with each value in the array in sequentially.
 */
function preg_replace_array(string $pattern, array $replacements, string $subject): string
{
    return preg_replace_callback($pattern, function () use (&$replacements) {
        foreach ($replacements as $key => $value) {
            return array_shift($replacements);
        }
    }, $subject);
}

/**
 * Resolve a service from the container.
 *
 * @template TClass
 *
 * @param callable|class-string<TClass>|string $abstract
 *
 * @return ($abstract is callable ? Closure : ($abstract is class-string<TClass> ? TClass : mixed))
 */
function resolve(string|callable $abstract, array $parameters = [])
{
    if (is_callable($abstract)) {
        return Closure::fromCallable($abstract);
    }

    return di($abstract, $parameters);
}

/**
 * Get an instance of the current request or an input item from the request.
 * @param null|array|string $key
 * @param mixed $default
 * @return ($key is null ? RequestInterface : ($key is array ? array : mixed))
 */
function request($key = null, $default = null)
{
    $request = di(RequestInterface::class);

    if (is_null($key)) {
        return $request;
    }

    if (is_array($key)) {
        return $request->inputs($key, value($default));
    }

    return $request->input($key, value($default));
}

/**
 * Return a new response from the application.
 *
 * @param null|array|string $content
 * @param int $status
 * @return PsrResponseInterface|ResponseInterface
 */
function response($content = '', $status = 200, array $headers = [])
{
    /** @var PsrResponseInterface|ResponseInterface $response */
    $response = di(ResponseInterface::class);

    if (func_num_args() === 0) {
        return $response;
    }

    if (is_array($content)) {
        $response->withAddedHeader('Content-Type', 'application/json');
        $content = json_encode($content);
    }

    return tap(
        $response->withBody(new SwooleStream((string) $content))
            ->withStatus($status),
        function ($response) use ($headers) {
            foreach ($headers as $name => $value) {
                $response->withAddedHeader($name, $value);
            }
        }
    );
}

/**
 * Catch a potential exception and return a default value.
 *
 * @template TValue
 * @template TFallback
 *
 * @param (callable(): TValue) $callback
 * @param (callable(Throwable): TFallback)|TFallback $rescue
 * @param null|(Closure(Throwable): void) $exceptionHandler
 * @return TValue|TFallback
 */
function rescue(callable $callback, mixed $rescue = null, ?Closure $exceptionHandler = null)
{
    try {
        return $callback();
    } catch (Throwable $e) {
        if ($exceptionHandler) {
            $exceptionHandler($e);
        }

        return value($rescue, $e);
    }
}

/**
 * Get / set the specified session value.
 *
 * If an array is passed as the key, we will assume you want to set an array of values.
 *
 * @param null|array|string $key
 * @param mixed $default
 * @return ($key is null ? SessionInterface : ($key is array ? void : mixed))
 */
function session($key = null, $default = null)
{
    $session = di(SessionInterface::class);

    if (is_null($key)) {
        return $session;
    }

    if (is_array($key)) {
        $session->put($key);
        return;
    }

    return $session->get($key, $default);
}

/**
 * Throw the given exception if the given condition is true.
 *
 * @template TValue
 * @template TException of \Throwable
 *
 * @param TValue $condition
 * @param TException|class-string<TException>|string $exception
 * @param mixed ...$parameters
 * @return TValue
 *
 * @throws TException
 */
function throw_if($condition, $exception, ...$parameters)
{
    if ($condition) {
        if (is_string($exception) && class_exists($exception)) {
            $exception = new $exception(...$parameters);
        }

        throw is_string($exception) ? new RuntimeException($exception) : $exception;
    }

    return $condition;
}

/**
 * Throw the given exception unless the given condition is true.
 *
 * @template TValue
 * @template TException of \Throwable
 *
 * @param TValue $condition
 * @param TException|class-string<TException>|string $exception
 * @param mixed ...$parameters
 * @return TValue
 *
 * @throws TException
 */
function throw_unless($condition, $exception, ...$parameters)
{
    if (! $condition) {
        if (is_string($exception) && class_exists($exception)) {
            $exception = new $exception(...$parameters);
        }

        throw is_string($exception) ? new RuntimeException($exception) : $exception;
    }

    return $condition;
}

/**
 * Transform the given value if it is present.
 *
 * @template TValue
 * @template TReturn
 * @template TDefault
 *
 * @param TValue $value
 * @param (callable(TValue): TReturn) $callback
 * @param TDefault|(callable(TValue): TDefault) $default
 * @return ($value is empty ? TDefault : TReturn)
 */
function transform($value, callable $callback, $default = null)
{
    if (filled($value)) {
        return $callback($value);
    }

    if (is_callable($default)) {
        return $default($value);
    }

    return $default;
}

/**
 * Create a new Validator instance.
 * @return ValidatorFactoryInterface|ValidatorInterface
 */
function validator(array $data = [], array $rules = [], array $messages = [], array $customAttributes = [])
{
    $factory = di(ValidatorFactoryInterface::class);

    if (func_num_args() === 0) {
        return $factory;
    }

    return $factory->make($data, $rules, $messages, $customAttributes);
}

/**
 * Get the current date / time plus the given number of weeks.
 */
function weeks(int $weeks): CarbonInterval
{
    return CarbonInterval::weeks($weeks);
}

/**
 * @param mixed $expr
 * @param mixed $value
 * @param mixed $default
 * @return mixed
 */
function when($expr, $value = null, $default = null)
{
    $result = value($expr) ? $value : $default;

    return $result instanceof Closure ? $result($expr) : $result;
}
