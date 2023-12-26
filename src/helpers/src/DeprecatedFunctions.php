<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use Carbon\Carbon;
use FriendsOfHyperf\AsyncTask\TaskInterface as AsyncTaskInterface;
use FriendsOfHyperf\Support\Environment;
use Hyperf\Amqp\Message\ProducerMessageInterface;
use Hyperf\AsyncQueue\JobInterface;
use Hyperf\Contract\SessionInterface;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\HttpMessage\Cookie\CookieJarInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Stringable\Stringable;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use longlang\phpkafka\Producer\ProduceMessage;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

if (! function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @template T
     *
     * @param callable|class-string<T> $abstract
     *
     * @return Closure|ContainerInterface|T
     * @deprecated since 3.1, use `\FriendsOfHyperf\Helpers\app` instead.
     */
    function app(string|callable $abstract = null, array $parameters = [])
    {
        return FriendsOfHyperf\Helpers\app($abstract, $parameters);
    }
}

if (! function_exists('array_is_list')) {
    /**
     * Determine if the given value is a list of items.
     * @return bool return true if the array keys are 0 .. count($array)-1 in that order. For other arrays, it returns false. For non-arrays, it throws a TypeError.
     */
    function array_is_list(array $array): bool
    {
        return FriendsOfHyperf\Helpers\array_is_list($array);
    }
}

if (! function_exists('base_path')) {
    /**
     * Get the path to the base of the install.
     * @deprecated since 3.1, use `\FriendsOfHyperf\Helpers\base_path` instead.
     */
    function base_path(string $path = ''): string
    {
        return FriendsOfHyperf\Helpers\base_path($path);
    }
}

if (! function_exists('blank')) {
    /**
     * Determine if the given value is "blank".
     *
     * @param mixed $value
     * @deprecated since 3.1, use `\FriendsOfHyperf\Helpers\blank` instead.
     */
    function blank($value): bool
    {
        return FriendsOfHyperf\Helpers\blank($value);
    }
}

if (! function_exists('cache')) {
    /**
     * Get / set the specified cache value.
     *
     * If an array is passed, we'll assume you want to put to the cache.
     *
     * @return CacheInterface|mixed
     * @throws Exception
     * @deprecated since 3.1, use `\FriendsOfHyperf\Helpers\cache` instead.
     */
    function cache()
    {
        return FriendsOfHyperf\Helpers\cache(...func_get_args());
    }
}

if (! function_exists('call_command')) {
    /**
     * Call command quickly.
     * @throws TypeError
     * @throws Exception
     * @deprecated since 3.1, please use `\FriendsOfHyperf\Helpers\Command\call` instead.
     */
    function call_command(string $command, array $arguments = []): int
    {
        return \FriendsOfHyperf\Helpers\Command\call($command, $arguments);
    }
}

if (! function_exists('cookie')) {
    /**
     * Create a new cookie instance.
     *
     * @return Cookie|CookieJarInterface
     * @deprecated since 3.1, use `\FriendsOfHyperf\Helpers\cookie` instead.
     */
    function cookie(?string $name = null, string $value = null, int $minutes = 0, string $path = null, string $domain = null, bool $secure = false, bool $httpOnly = true, bool $raw = false, ?string $sameSite = null)
    {
        return FriendsOfHyperf\Helpers\cookie($name, $value, $minutes, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }
}

if (! function_exists('class_namespace')) {
    /**
     * Get the class "namespace" of the given object / class.
     *
     * @param object|string $class
     * @deprecated since 3.1, use `\FriendsOfHyperf\Helpers\class_namespace` instead.
     */
    function class_namespace($class): string
    {
        return FriendsOfHyperf\Helpers\class_namespace($class);
    }
}

if (! function_exists('di')) {
    /**
     * Get the available container instance.
     *
     * @template T
     *
     * @param class-string<T> $abstract
     *
     * @return ContainerInterface|T
     * @deprecated since 3.1, use `\FriendsOfHyperf\Helpers\di` instead.
     */
    function di(string $abstract = null, array $parameters = [])
    {
        return FriendsOfHyperf\Helpers\di($abstract, $parameters);
    }
}

if (! function_exists('dispatch')) {
    /**
     * @param AsyncTaskInterface|Closure|JobInterface|ProduceMessage|ProducerMessageInterface $job
     * @return bool
     * @deprecated since 3.1, use `\FriendsOfHyperf\Helpers\dispatch` instead.
     */
    function dispatch($job, ...$arguments)
    {
        return FriendsOfHyperf\Helpers\dispatch($job, ...$arguments);
    }
}

if (! function_exists('environment')) {
    /**
     * @param mixed $environments
     * @return bool|Environment
     * @throws TypeError
     * @deprecated since 3.1, use `\FriendsOfHyperf\Helpers\environment` instead.
     */
    function environment(...$environments)
    {
        return FriendsOfHyperf\Helpers\environment(...$environments);
    }
}

if (! function_exists('event')) {
    /**
     * Dispatch an event and call the listeners.
     *
     * @template T of object
     *
     * @param T $event
     *
     * @return T
     * @deprecated since 3.1, use `\FriendsOfHyperf\Helpers\event` instead.
     */
    function event(object $event)
    {
        return FriendsOfHyperf\Helpers\event($event);
    }
}

if (! function_exists('filled')) {
    /**
     * Determine if a value is "filled".
     *
     * @param mixed $value
     * @deprecated since 3.1, use `\FriendsOfHyperf\Helpers\filled` instead.
     */
    function filled($value): bool
    {
        return FriendsOfHyperf\Helpers\filled($value);
    }
}

if (! function_exists('info')) {
    /**
     * @param string|\Stringable $message
     * @throws TypeError
     * @deprecated since 3.1, use `\FriendsOfHyperf\Helpers\info` instead.
     */
    function info($message, array $context = [], bool $backtrace = false)
    {
        return FriendsOfHyperf\Helpers\info($message, $context, $backtrace);
    }
}

if (! function_exists('logger')) {
    /**
     * @param string|\Stringable|null $message
     * @throws TypeError
     * @deprecated since 3.1, use `\FriendsOfHyperf\Helpers\logger` instead.
     */
    function logger($message = null, array $context = [], bool $backtrace = false)
    {
        if (is_null($message)) {
            return FriendsOfHyperf\Helpers\logger();
        }
        FriendsOfHyperf\Helpers\logger($message, $context, $backtrace);
    }
}

if (! function_exists('logs')) {
    /**
     * @throws TypeError
     * @deprecated since 3.1, use `\FriendsOfHyperf\Helpers\logs` instead.
     */
    function logs(string $name = 'hyperf', string $group = 'default'): LoggerInterface
    {
        return FriendsOfHyperf\Helpers\logs($name, $group);
    }
}

if (! function_exists('now')) {
    /**
     * Create a new Carbon instance for the current time.
     *
     * @param DateTimeZone|string|null $tz
     * @deprecated since 3.1, use `\FriendsOfHyperf\Helpers\now` instead.
     */
    function now($tz = null): Carbon
    {
        return FriendsOfHyperf\Helpers\now($tz);
    }
}

if (! function_exists('object_get')) {
    /**
     * Get an item from an object using "dot" notation.
     *
     * @template T of object
     *
     * @param T $object
     * @param string|null $key
     * @param mixed $default
     * @return mixed|T
     * @deprecated since 3.1, use `\FriendsOfHyperf\Helpers\object_get` instead.
     */
    function object_get($object, $key = '', $default = null)
    {
        return FriendsOfHyperf\Helpers\object_get($object, $key, $default);
    }
}

if (! function_exists('preg_replace_array')) {
    /**
     * Replace a given pattern with each value in the array in sequentially.
     * @deprecated since 3.1, use `\FriendsOfHyperf\Helpers\preg_replace_array` instead.
     */
    function preg_replace_array(string $pattern, array $replacements, string $subject): string
    {
        return FriendsOfHyperf\Helpers\preg_replace_array($pattern, $replacements, $subject);
    }
}

if (! function_exists('resolve')) {
    /**
     * Resolve a service from the container.
     *
     * @template T
     *
     * @param callable|class-string<T> $abstract
     *
     * @return Closure|ContainerInterface|T
     * @deprecated since 3.1, use `\FriendsOfHyperf\Helpers\resolve` instead.
     */
    function resolve(string|callable $abstract, array $parameters = [])
    {
        return FriendsOfHyperf\Helpers\resolve($abstract, $parameters);
    }
}

if (! function_exists('request')) {
    /**
     * Get an instance of the current request or an input item from the request.
     * @param array|string|null $key
     * @param mixed $default
     * @return array|mixed|RequestInterface
     * @throws TypeError
     * @deprecated since 3.1, use `\FriendsOfHyperf\Helpers\request` instead.
     */
    function request($key = null, $default = null)
    {
        return FriendsOfHyperf\Helpers\request($key, $default);
    }
}

if (! function_exists('response')) {
    /**
     * Return a new response from the application.
     *
     * @param array|string|null $content
     * @param int $status
     * @return PsrResponseInterface|ResponseInterface
     * @deprecated since 3.1, use `\FriendsOfHyperf\Helpers\response` instead.
     */
    function response($content = '', $status = 200, array $headers = [])
    {
        return FriendsOfHyperf\Helpers\response($content, $status, $headers);
    }
}

if (! function_exists('session')) {
    /**
     * Get / set the specified session value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param array|string|null $key
     * @param mixed $default
     * @return mixed|SessionInterface
     * @deprecated since 3.1, use `\FriendsOfHyperf\Helpers\session` instead.
     */
    function session($key = null, $default = null)
    {
        return FriendsOfHyperf\Helpers\session($key, $default);
    }
}

if (! function_exists('str')) {
    /**
     * Get a new stringable object from the given string.
     *
     * @param string|null $string
     * @return mixed|Stringable
     * @deprecated since 3.1, use `\Hyperf\Stringable\str` instead.
     */
    function str($string = null)
    {
        return Hyperf\Stringable\str($string);
    }
}

if (! function_exists('today')) {
    /**
     * Create a new Carbon instance for the current date.
     *
     * @param DateTimeZone|string|null $tz
     * @deprecated since 3.1, use `\FriendsOfHyperf\Helpers\today` instead.
     */
    function today($tz = null): Carbon
    {
        return FriendsOfHyperf\Helpers\today($tz);
    }
}

if (! function_exists('throw_if')) {
    /**
     * Throw the given exception if the given condition is true.
     *
     * @template T
     *
     * @param T $condition
     * @param string|\Throwable $exception
     * @param array ...$parameters
     * @return T
     * @throws Throwable
     * @deprecated since 3.1, use `\FriendsOfHyperf\Helpers\throw_if` instead.
     */
    function throw_if($condition, $exception, ...$parameters)
    {
        return FriendsOfHyperf\Helpers\throw_if($condition, $exception, ...$parameters);
    }
}

if (! function_exists('throw_unless')) {
    /**
     * Throw the given exception unless the given condition is true.
     *
     * @template T
     *
     * @param T $condition
     * @param string|\Throwable $exception
     * @param array ...$parameters
     * @return T
     * @throws Throwable
     * @deprecated since 3.1, use `\FriendsOfHyperf\Helpers\throw_unless` instead.
     */
    function throw_unless($condition, $exception, ...$parameters)
    {
        return FriendsOfHyperf\Helpers\throw_unless($condition, $exception, ...$parameters);
    }
}

if (! function_exists('validator')) {
    /**
     * Create a new Validator instance.
     * @return ValidatorFactoryInterface|ValidatorInterface
     * @throws TypeError
     * @deprecated since 3.1, use `\FriendsOfHyperf\Helpers\validator` instead.
     */
    function validator(array $data = [], array $rules = [], array $messages = [], array $customAttributes = [])
    {
        return FriendsOfHyperf\Helpers\validator($data, $rules, $messages, $customAttributes);
    }
}

if (! function_exists('when')) {
    /**
     * @param mixed $expr
     * @param mixed $value
     * @param mixed $default
     * @return mixed
     * @deprecated since 3.1, use `\FriendsOfHyperf\Helpers\when` instead.
     */
    function when($expr, $value = null, $default = null)
    {
        return FriendsOfHyperf\Helpers\when($expr, $value, $default);
    }
}

if (! function_exists('get_client_ip')) {
    /**
     * Get client IP.
     * @throws TypeError
     * @deprecated since 3.1, use `\FriendsOfHyperf\Helpers\get_client_ip` instead.
     */
    function get_client_ip(): string
    {
        return FriendsOfHyperf\Helpers\get_client_ip();
    }
}
