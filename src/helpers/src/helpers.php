<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/1.x/README.md
 * @contact  huangdijia@gmail.com
 */
if (! function_exists('app')) {
    /**
     * @throws TypeError
     * @return mixed|\Psr\Container\ContainerInterface
     */
    function app(string $abstract = null, array $parameters = [])
    {
        if (\Hyperf\Utils\ApplicationContext::hasContainer()) {
            /** @var \Hyperf\Contract\ContainerInterface $container */
            $container = \Hyperf\Utils\ApplicationContext::getContainer();

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

if (! function_exists('blank')) {
    /**
     * Determine if the given value is "blank".
     *
     * @param mixed $value
     * @return bool
     */
    function blank($value)
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
     * @throws \Exception
     * @return mixed|\Psr\SimpleCache\CacheInterface
     */
    function cache()
    {
        $arguments = func_get_args();
        $cache = app(\Psr\SimpleCache\CacheInterface::class);

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
     * @return int
     */
    function call_command(string $command, array $arguments = [])
    {
        $arguments['command'] = $command;
        $input = new \Symfony\Component\Console\Input\ArrayInput($arguments);
        $output = new \Symfony\Component\Console\Output\NullOutput();

        /** @var \Symfony\Component\Console\Application $application */
        $application = app(\Hyperf\Contract\ApplicationInterface::class);
        $application->setAutoExit(false);

        return $application->run($input, $output);
    }
}

if (! function_exists('cookie')) {
    /**
     * Create a new cookie instance.
     *
     * @param null|string $name
     * @param null|string $value
     * @param int $minutes
     * @param null|string $path
     * @param null|string $domain
     * @param null|bool $secure
     * @param bool $httpOnly
     * @param bool $raw
     * @param null|string $sameSite
     * @return \Hyperf\HttpMessage\Cookie\Cookie|\Hyperf\HttpMessage\Cookie\CookieJarInterface
     */
    function cookie($name = null, $value = null, $minutes = 0, $path = null, $domain = null, $secure = null, $httpOnly = true, $raw = false, $sameSite = null)
    {
        if (is_null($name)) {
            return app(\Hyperf\HttpMessage\Cookie\CookieJarInterface::class);
        }

        $time = ($minutes == 0) ? 0 : $minutes * 60;

        return new \Hyperf\HttpMessage\Cookie\Cookie($name, $value, $time, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }
}

if (! function_exists('class_namespace')) {
    /**
     * Get the class "namespace" of the given object / class.
     *
     * @param object|string $class
     * @return string
     */
    function class_namespace($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return \Hyperf\Utils\Str::classNamespace($class);
    }
}

if (! function_exists('dispatch')) {
    /**
     * @param Closure|\Hyperf\Amqp\Message\ProducerMessageInterface|\Hyperf\AsyncQueue\JobInterface|\longlang\phpkafka\Producer\ProduceMessage $job
     * @throws TypeError
     * @throws InvalidDriverException
     * @throws InvalidArgumentException
     * @return bool
     */
    function dispatch($job, ...$arguments)
    {
        if ($job instanceof Closure) {
            $job = new \FriendsOfHyperf\Helpers\Foundation\AsyncQueue\ClosureJob($job, (int) ($arguments[2] ?? 0));
        }

        if ($job instanceof \Hyperf\AsyncQueue\JobInterface) {
            /** @var \Hyperf\AsyncQueue\Driver\DriverInterface $driver */
            $driver = app(\Hyperf\AsyncQueue\Driver\DriverFactory::class)->get((string) ($arguments[0] ?? $job->queue ?? 'default'));
            return $driver->push($job, (int) ($arguments[1] ?? $job->delay ?? 0));
        }

        if ($job instanceof \Hyperf\Amqp\Message\ProducerMessageInterface) {
            /** @var \Hyperf\Amqp\Producer $producer */
            $producer = app(\Hyperf\Amqp\Producer::class);
            return $producer->produce($job, ...$arguments);
        }

        if ($job instanceof \longlang\phpkafka\Producer\ProduceMessage) {
            /** @var \Hyperf\Kafka\Producer $producer */
            $producer = app(\Hyperf\Kafka\ProducerManager::class)->getProducer((string) ($arguments[0] ?? 'default'));
            return $producer->sendBatch([$job]);
        }

        throw new \InvalidArgumentException('Not Support job type.');
    }
}

if (! function_exists('dispatch_now')) {
    /**
     * @param \Hyperf\AsyncQueue\JobInterface $job
     * @throws TypeError
     * @throws InvalidDriverException
     * @throws InvalidArgumentException
     * @return mixed
     * @deprecated 0.2.0
     */
    function dispatch_now($job)
    {
        if ($job instanceof \Hyperf\AsyncQueue\JobInterface) {
            return $job->handle();
        }

        throw new \InvalidArgumentException('Not Support job type.');
    }
}

if (! function_exists('environment')) {
    /**
     * @param mixed $environments
     * @throws TypeError
     * @return bool|\FriendsOfHyperf\Helpers\Foundation\Environment
     */
    function environment(...$environments)
    {
        $environment = app(\FriendsOfHyperf\Helpers\Foundation\Environment::class);

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
     * @return object
     */
    function event(object $event)
    {
        return app(\Psr\EventDispatcher\EventDispatcherInterface::class)->dispatch($event);
    }
}

if (! function_exists('filled')) {
    /**
     * Determine if a value is "filled".
     *
     * @param mixed $value
     * @return bool
     */
    function filled($value)
    {
        return ! blank($value);
    }
}

if (! function_exists('info')) {
    /**
     * @param string $message
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
     * @param null|string $message
     * @throws TypeError
     * @return \Psr\Log\LoggerInterface|void
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
     * @param string $name
     * @param string $group
     * @throws TypeError
     * @return \Psr\Log\LoggerInterface
     */
    function logs($name = 'hyperf', $group = 'default')
    {
        return app(\Hyperf\Logger\LoggerFactory::class)->get($name, $group);
    }
}

if (! function_exists('now')) {
    /**
     * Create a new Carbon instance for the current time.
     *
     * @param null|\DateTimeZone|string $tz
     * @return \Carbon\Carbon
     */
    function now($tz = null)
    {
        return \Carbon\Carbon::now($tz);
    }
}

if (! function_exists('object_get')) {
    /**
     * Get an item from an object using "dot" notation.
     *
     * @param object $object
     * @param null|string $key
     * @param mixed $default
     * @return mixed
     */
    function object_get($object, $key, $default = null)
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
     *
     * @param string $pattern
     * @param string $subject
     * @return string
     */
    function preg_replace_array($pattern, array $replacements, $subject)
    {
        return preg_replace_callback($pattern, function () use (&$replacements) {
            foreach ($replacements as $key => $value) {
                return array_shift($replacements);
            }
        }, $subject);
    }
}

if (! function_exists('request')) {
    /**
     * Get an instance of the current request or an input item from the request.
     * @param null|array|string $key
     * @param mixed $default
     * @throws TypeError
     * @return array|\Hyperf\HttpServer\Contract\RequestInterface|mixed
     */
    function request($key = null, $default = null)
    {
        /** @var \Hyperf\HttpServer\Contract\RequestInterface $request */
        $request = app(\Hyperf\HttpServer\Contract\RequestInterface::class);

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
     * @return \Hyperf\HttpServer\Contract\ResponseInterface|\Psr\Http\Message\ResponseInterface
     */
    function response($content = '', $status = 200, array $headers = [])
    {
        /** @var \Hyperf\HttpServer\Contract\ResponseInterface|\Psr\Http\Message\ResponseInterface $response */
        $response = app(\Hyperf\HttpServer\Contract\ResponseInterface::class);

        if (func_num_args() === 0) {
            return $response;
        }

        if (is_array($content)) {
            $response->withAddedHeader('Content-Type', 'application/json');
            $content = json_encode($content);
        }

        return tap(
            $response->withBody(new \Hyperf\HttpMessage\Stream\SwooleStream((string) $content))
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
     * @return \Hyperf\Contract\SessionInterface|mixed
     */
    function session($key = null, $default = null)
    {
        $session = app(\Hyperf\Contract\SessionInterface::class);

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
     * @return \Hyperf\Utils\Stringable
     */
    function str($string = null)
    {
        if (func_num_args() === 0) {
            return new class() {
                public function __call($method, $parameters)
                {
                    return \Hyperf\Utils\Str::$method(...$parameters);
                }

                public function __toString()
                {
                    return '';
                }
            };
        }

        return \Hyperf\Utils\Str::of($string);
    }
}

if (! function_exists('today')) {
    /**
     * Create a new Carbon instance for the current date.
     *
     * @param null|\DateTimeZone|string $tz
     * @return \Carbon\Carbon
     */
    function today($tz = null)
    {
        return \Carbon\Carbon::today($tz);
    }
}

if (! function_exists('throw_if')) {
    /**
     * Throw the given exception if the given condition is true.
     *
     * @param mixed $condition
     * @param string|\Throwable $exception
     * @param array ...$parameters
     * @throws \Throwable
     * @return mixed
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
     * @param mixed $condition
     * @param string|\Throwable $exception
     * @param array ...$parameters
     * @throws \Throwable
     * @return mixed
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
     * @throws TypeError
     * @return \Hyperf\Contract\ValidatorInterface|\Hyperf\Validation\Contract\ValidatorFactoryInterface
     */
    function validator(array $data = [], array $rules = [], array $messages = [], array $customAttributes = [])
    {
        /** @var \Hyperf\Validation\Contract\ValidatorFactoryInterface $factory */
        $factory = app(\Hyperf\Validation\Contract\ValidatorFactoryInterface::class);

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
        /** @var \Hyperf\HttpServer\Contract\RequestInterface $request */
        $request = app(\Hyperf\HttpServer\Contract\RequestInterface::class);
        return $request->getHeaderLine('x-real-ip') ?: $request->server('remote_addr');
    }
}
