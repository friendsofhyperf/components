<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Http\Client;

use Closure;

use function Hyperf\Support\make;
use function Hyperf\Tappable\tap;

/**
 * @method static Factory globalMiddleware(callable $middleware)
 * @method static Factory globalRequestMiddleware(callable $middleware)
 * @method static Factory globalResponseMiddleware(callable $middleware)
 * @method static \GuzzleHttp\Promise\PromiseInterface response(array|string|null $body = null, int $status = 200, array $headers = [])
 * @method static ResponseSequence sequence(array $responses = [])
 * @method static Factory allowStrayRequests()
 * @method static void recordRequestResponsePair(Request $request, Response $response)
 * @method static void assertSent(callable $callback)
 * @method static void assertSentInOrder(array $callbacks)
 * @method static void assertNotSent(callable $callback)
 * @method static void assertNothingSent()
 * @method static void assertSentCount(int $count)
 * @method static void assertSequencesAreEmpty()
 * @method static \Hyperf\Collection\Collection recorded(callable $callback = null)
 * @method static \Hyperf\Contract\DispatcherInterface|null getDispatcher()
 * @method static array getGlobalMiddleware()
 * @method static void macro(string $name, object|callable $macro)
 * @method static void mixin(object $mixin, bool $replace = true)
 * @method static bool hasMacro(string $name)
 * @method static void flushMacros()
 * @method static mixed macroCall(string $method, array $parameters)
 * @method static PendingRequest baseUrl(string $url)
 * @method static PendingRequest withBody(string $content, string $contentType = 'application/json')
 * @method static PendingRequest asJson()
 * @method static PendingRequest asForm()
 * @method static PendingRequest attach(string|array $name, string|resource $contents = '', string|null $filename = null, array $headers = [])
 * @method static PendingRequest asMultipart()
 * @method static PendingRequest bodyFormat(string $format)
 * @method static PendingRequest withQueryParameters(array $parameters)
 * @method static PendingRequest contentType(string $contentType)
 * @method static PendingRequest acceptJson()
 * @method static PendingRequest accept(string $contentType)
 * @method static PendingRequest withHeaders(array $headers)
 * @method static PendingRequest withHeader(string $name, mixed $value)
 * @method static PendingRequest replaceHeaders(array $headers)
 * @method static PendingRequest withBasicAuth(string $username, string $password)
 * @method static PendingRequest withDigestAuth(string $username, string $password)
 * @method static PendingRequest withToken(string $token, string $type = 'Bearer')
 * @method static PendingRequest withUserAgent(string|bool $userAgent)
 * @method static PendingRequest withUrlParameters(array $parameters = [])
 * @method static PendingRequest withCookies(array $cookies, string $domain)
 * @method static PendingRequest maxRedirects(int $max)
 * @method static PendingRequest withoutRedirecting()
 * @method static PendingRequest withoutVerifying()
 * @method static PendingRequest sink(string|resource $to)
 * @method static PendingRequest timeout(int $seconds)
 * @method static PendingRequest connectTimeout(int $seconds)
 * @method static PendingRequest retry(int $times, \Closure|int $sleepMilliseconds = 0, callable|null $when = null, bool $throw = true)
 * @method static PendingRequest withOptions(array $options)
 * @method static PendingRequest withMiddleware(callable $middleware)
 * @method static PendingRequest withRequestMiddleware(callable $middleware)
 * @method static PendingRequest withResponseMiddleware(callable $middleware)
 * @method static PendingRequest beforeSending(callable $callback)
 * @method static PendingRequest throw(callable|null $callback = null)
 * @method static PendingRequest throwIf(callable|bool $condition, callable|null $throwCallback = null)
 * @method static PendingRequest throwUnless(bool $condition)
 * @method static PendingRequest dump()
 * @method static PendingRequest dd()
 * @method static Response get(string $url, array|string|null $query = null)
 * @method static Response head(string $url, array|string|null $query = null)
 * @method static Response post(string $url, array $data = [])
 * @method static Response patch(string $url, array $data = [])
 * @method static Response put(string $url, array $data = [])
 * @method static Response delete(string $url, array $data = [])
 * @method static array pool(callable $callback)
 * @method static Response send(string $method, string $url, array $options = [])
 * @method static \GuzzleHttp\Client buildClient()
 * @method static \GuzzleHttp\Client createClient(\GuzzleHttp\HandlerStack $handlerStack)
 * @method static \GuzzleHttp\HandlerStack buildHandlerStack()
 * @method static \GuzzleHttp\HandlerStack pushHandlers(\GuzzleHttp\HandlerStack $handlerStack)
 * @method static \Closure buildBeforeSendingHandler()
 * @method static \Closure buildRecorderHandler()
 * @method static \Closure buildStubHandler()
 * @method static \GuzzleHttp\Psr7\RequestInterface runBeforeSendingCallbacks(\GuzzleHttp\Psr7\RequestInterface $request, array $options)
 * @method static array mergeOptions(array ...$options)
 * @method static PendingRequest stub(callable $callback)
 * @method static PendingRequest async(bool $async = true)
 * @method static \GuzzleHttp\Promise\PromiseInterface|null getPromise()
 * @method static PendingRequest setClient(\GuzzleHttp\Client $client)
 * @method static PendingRequest setHandler(callable $handler)
 * @method static array getOptions()
 * @method static PendingRequest|mixed when(\Closure|mixed|null $value = null, callable|null $callback = null, callable|null $default = null)
 * @method static PendingRequest|mixed unless(\Closure|mixed|null $value = null, callable|null $callback = null, callable|null $default = null)
 *
 * @see Factory
 */
class Http
{
    /**
     * The resolved object instances.
     *
     * @var array
     */
    protected static $resolvedInstance;

    public static function __callStatic(string $name, array $arguments)
    {
        return static::getFacadeRoot()->{$name}(...$arguments);
    }

    /**
     * Get the root object behind the facade.
     *
     * @return mixed
     */
    public static function getFacadeRoot()
    {
        return static::resolveFacadeInstance(static::getFacadeAccessor());
    }

    /**
     * Register a stub callable that will intercept requests and be able to return stub responses.
     *
     * @param array|Closure $callback
     * @return Factory
     */
    public static function fake($callback = null)
    {
        return tap(static::getFacadeRoot(), function ($fake) use ($callback) {
            static::swap($fake->fake($callback));
        });
    }

    /**
     * Register a response sequence for the given URL pattern.
     *
     * @return ResponseSequence
     */
    public static function fakeSequence(string $urlPattern = '*')
    {
        $fake = tap(static::getFacadeRoot(), function ($fake) {
            static::swap($fake);
        });

        return $fake->fakeSequence($urlPattern);
    }

    /**
     * Indicate that an exception should be thrown if any request is not faked.
     *
     * @return Factory
     */
    public static function preventStrayRequests()
    {
        return tap(static::getFacadeRoot(), function ($fake) {
            static::swap($fake->preventStrayRequests());
        });
    }

    /**
     * Stub the given URL using the given callback.
     *
     * @param string $url
     * @param callable|\GuzzleHttp\Promise\PromiseInterface|Response $callback
     * @return Factory
     */
    public static function stubUrl($url, $callback)
    {
        return tap(static::getFacadeRoot(), function ($fake) use ($url, $callback) {
            static::swap($fake->stubUrl($url, $callback));
        });
    }

    /**
     * Hotswap the underlying instance behind the facade.
     *
     * @param mixed $instance
     */
    public static function swap($instance)
    {
        static::$resolvedInstance[static::getFacadeAccessor()] = $instance;
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Factory::class;
    }

    /**
     * Resolve the facade root instance from the container.
     *
     * @param string $name
     * @return mixed
     */
    protected static function resolveFacadeInstance($name)
    {
        if (isset(static::$resolvedInstance[$name])) {
            return static::$resolvedInstance[$name];
        }

        return static::$resolvedInstance[$name] = make($name);
    }
}
