<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
use Carbon\Carbon;
use FriendsOfHyperf\AsyncTask\Task as AsyncTask;
use FriendsOfHyperf\AsyncTask\TaskInterface as AsyncTaskInterface;
use FriendsOfHyperf\Helpers\Foundation\AsyncQueue\ClosureJob;
use FriendsOfHyperf\Helpers\Foundation\Environment;
use Hyperf\Amqp\Message\ProducerMessageInterface;
use Hyperf\Amqp\Producer;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\JobInterface;
use Hyperf\Contract\ApplicationInterface;
use Hyperf\Contract\SessionInterface;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\HttpMessage\Cookie\CookieJarInterface;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Kafka\ProducerManager;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Str;
use Hyperf\Utils\Stringable;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use longlang\phpkafka\Producer\ProduceMessage;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

if (! function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @template T
     *
     * @param callable|class-string<T> $abstract
     *
     * @return Closure|ContainerInterface|T
     */
    function app(string|callable $abstract = null, array $parameters = [])
    {
        if (is_callable($abstract)) {
            return Closure::fromCallable($abstract);
        }

        return di($abstract, $parameters);
    }
}

if (! function_exists('array_is_list')) {
    /**
     * Determine if the given value is a list of items.
     * @return bool return true if the array keys are 0 .. count($array)-1 in that order. For other arrays, it returns false. For non-arrays, it throws a TypeError.
     */
    function array_is_list(array $array): bool
    {
        if ($array === [] || $array === array_values($array)) {
            return true;
        }

        $nextKey = -1;

        foreach ($array as $k => $v) {
            if ($k !== ++$nextKey) {
                return false;
            }
        }

        return true;
    }
}

if (! function_exists('base_path')) {
    /**
     * Get the path to the base of the install.
     */
    function base_path(string $path = ''): string
    {
        return BASE_PATH . ($path ? '/' . $path : $path);
    }
}

if (! function_exists('blank')) {
    /**
     * Determine if the given value is "blank".
     *
     * @param mixed $value
     */
    function blank($value): bool
    {
        if (is_null($value)) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        if (is_numeric($value) || is_bool($value)) {
            return false;
        }

        if ($value instanceof Countable) {
            return count($value) === 0;
        }

        return empty($value);
    }
}

if (! function_exists('cache')) {
    /**
     * Get / set the specified cache value.
     *
     * If an array is passed, we'll assume you want to put to the cache.
     *
     * @param  dynamic  key|key,default|data,expiration|null
     * @param null|string $key
     * @param null|mixed $value
     * @param null|DateInterval|DateTimeInterface|int $ttl
     * @return CacheInterface|mixed
     * @throws \Exception
     */
    function cache()
    {
        $arguments = func_get_args();
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
}

if (! function_exists('call_command')) {
    /**
     * Call command quickly.
     * @throws TypeError
     * @throws Exception
     */
    function call_command(string $command, array $arguments = []): int
    {
        $arguments['command'] = $command;
        $input = new ArrayInput($arguments);
        $output = new NullOutput();

        /** @var \Symfony\Component\Console\Application $application */
        $application = di(ApplicationInterface::class);
        $application->setAutoExit(false);

        return $application->run($input, $output);
    }
}

if (! function_exists('cookie')) {
    /**
     * Create a new cookie instance.
     *
     * @return Cookie|CookieJarInterface
     */
    function cookie(?string $name = null, string $value = null, int $minutes = 0, string $path = null, string $domain = null, bool $secure = false, bool $httpOnly = true, bool $raw = false, ?string $sameSite = null)
    {
        if (is_null($name)) {
            return di(CookieJarInterface::class);
        }

        $time = ($minutes == 0) ? 0 : $minutes * 60;

        return new Cookie($name, $value, $time, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }
}

if (! function_exists('class_namespace')) {
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
     */
    function di(string $abstract = null, array $parameters = [])
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
            throw new \InvalidArgumentException('Invalid argument $abstract');
        }

        return new $abstract(...array_values($parameters));
    }
}

if (! function_exists('dispatch')) {
    /**
     * @param AsyncTaskInterface|Closure|JobInterface|ProduceMessage|ProducerMessageInterface $job
     * @return bool
     * @throws TypeError
     * @throws InvalidDriverException
     * @throws InvalidArgumentException
     */
    function dispatch($job, ...$arguments)
    {
        if ($job instanceof Closure) {
            $job = new ClosureJob($job, (int) ($arguments[2] ?? 0));
        }

        return match (true) {
            $job instanceof JobInterface => di(DriverFactory::class)
                ->get((string) ($arguments[0] ?? $job->queue ?? 'default'))
                ->push($job, (int) ($arguments[1] ?? $job->delay ?? 0)),
            $job instanceof ProducerMessageInterface => di(Producer::class)
                ->produce($job, ...$arguments),
            $job instanceof ProduceMessage => di(ProducerManager::class)
                ->getProducer((string) ($arguments[0] ?? 'default'))
                ->sendBatch([$job]),
            $job instanceof AsyncTaskInterface => AsyncTask::deliver($job, ...$arguments),
            default => throw new \InvalidArgumentException('Not Support job type.')
        };
    }
}

if (! function_exists('environment')) {
    /**
     * @param mixed $environments
     * @return bool|Environment
     * @throws TypeError
     */
    function environment(...$environments)
    {
        $environment = di(Environment::class);

        if (count($environments) > 0) {
            return $environment->environment(...$environments);
        }

        return $environment;
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
     */
    function event(object $event)
    {
        return di(EventDispatcherInterface::class)->dispatch($event);
    }
}

if (! function_exists('filled')) {
    /**
     * Determine if a value is "filled".
     *
     * @param mixed $value
     */
    function filled($value): bool
    {
        return ! blank($value);
    }
}

if (! function_exists('info')) {
    /**
     * @param string|\Stringable $message
     * @throws TypeError
     */
    function info($message, array $context = [], bool $backtrace = false)
    {
        if ($backtrace) {
            $traces = debug_backtrace();
            $context['backtrace'] = sprintf('%s:%s', $traces[0]['file'], $traces[0]['line']);
        }

        return logs()->info($message, $context);
    }
}

if (! function_exists('logger')) {
    /**
     * @param null|string|\Stringable $message
     * @return LoggerInterface|void
     * @throws TypeError
     */
    function logger($message = null, array $context = [], bool $backtrace = false)
    {
        if (is_null($message)) {
            return logs();
        }

        if ($backtrace) {
            $traces = debug_backtrace();
            $context['backtrace'] = sprintf('%s:%s', $traces['file'], $traces['line']);
        }

        return logs()->debug($message, $context);
    }
}

if (! function_exists('logs')) {
    /**
     * @throws TypeError
     */
    function logs(string $name = 'hyperf', string $group = 'default'): LoggerInterface
    {
        return di(LoggerFactory::class)->get($name, $group);
    }
}

if (! function_exists('now')) {
    /**
     * Create a new Carbon instance for the current time.
     *
     * @param null|DateTimeZone|string $tz
     */
    function now($tz = null): Carbon
    {
        return Carbon::now($tz);
    }
}

if (! function_exists('object_get')) {
    /**
     * Get an item from an object using "dot" notation.
     *
     * @template T of object
     *
     * @param T $object
     * @param null|string $key
     * @param mixed $default
     * @return mixed|T
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
}

if (! function_exists('preg_replace_array')) {
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
     */
    function resolve(string|callable $abstract, array $parameters = [])
    {
        if (is_callable($abstract)) {
            return Closure::fromCallable($abstract);
        }

        return di($abstract, $parameters);
    }
}

if (! function_exists('request')) {
    /**
     * Get an instance of the current request or an input item from the request.
     * @param null|array|string $key
     * @param mixed $default
     * @return array|mixed|RequestInterface
     * @throws TypeError
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
}

if (! function_exists('response')) {
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
}

if (! function_exists('session')) {
    /**
     * Get / set the specified session value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param null|array|string $key
     * @param mixed $default
     * @return mixed|SessionInterface
     */
    function session($key = null, $default = null)
    {
        $session = di(SessionInterface::class);

        if (is_null($key)) {
            return $session;
        }

        if (is_array($key)) {
            return $session->put($key);
        }

        return $session->get($key, $default);
    }
}

if (! function_exists('str')) {
    /**
     * Get a new stringable object from the given string.
     *
     * @param null|string $string
     * @return mixed|Stringable
     */
    function str($string = null)
    {
        if (func_num_args() === 0) {
            return new class() {
                public function __call($method, $parameters)
                {
                    return Str::$method(...$parameters);
                }

                public function __toString()
                {
                    return '';
                }
            };
        }

        return Str::of($string);
    }
}

if (! function_exists('today')) {
    /**
     * Create a new Carbon instance for the current date.
     *
     * @param null|\DateTimeZone|string $tz
     */
    function today($tz = null): Carbon
    {
        return Carbon::today($tz);
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
     * @throws \Throwable
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
     * @throws \Throwable
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
}

if (! function_exists('validator')) {
    /**
     * Create a new Validator instance.
     * @return ValidatorFactoryInterface|ValidatorInterface
     * @throws TypeError
     */
    function validator(array $data = [], array $rules = [], array $messages = [], array $customAttributes = [])
    {
        $factory = di(ValidatorFactoryInterface::class);

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($data, $rules, $messages, $customAttributes);
    }
}

if (! function_exists('when')) {
    /**
     * @param mixed $expr
     * @param mixed $value
     * @param mixed $default
     * @return mixed
     */
    function when($expr, $value = null, $default = null)
    {
        $result = value($expr) ? $value : $default;

        return $result instanceof \Closure ? $result($expr) : $result;
    }
}

if (! function_exists('get_client_ip')) {
    /**
     * Get client IP.
     * @throws TypeError
     */
    function get_client_ip(): string
    {
        /** @var RequestInterface $request */
        $request = di(RequestInterface::class);
        return $request->getHeaderLine('x-real-ip') ?: $request->server('remote_addr');
    }
}
