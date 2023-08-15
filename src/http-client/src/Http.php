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
 * @method static \GuzzleHttp\Promise\PromiseInterface response($body = null, $status = 200, $headers = [])
 * @method static Factory fake($callback = null)
 * @method static PendingRequest accept(string $contentType)
 * @method static PendingRequest acceptJson()
 * @method static PendingRequest asForm()
 * @method static PendingRequest asJson()
 * @method static PendingRequest asMultipart()
 * @method static PendingRequest async()
 * @method static PendingRequest attach(array|string $name, string $contents = '', string|null $filename = null, array $headers = [])
 * @method static PendingRequest baseUrl(string $url)
 * @method static PendingRequest beforeSending(callable $callback)
 * @method static PendingRequest bodyFormat(string $format)
 * @method static PendingRequest contentType(string $contentType)
 * @method static PendingRequest dd()
 * @method static PendingRequest dump()
 * @method static PendingRequest maxRedirects(int $max)
 * @method static PendingRequest sink(resource|string $to)
 * @method static PendingRequest stub(callable $callback)
 * @method static PendingRequest timeout(int $seconds)
 * @method static PendingRequest connectTimeout(int $seconds)
 * @method static PendingRequest onHeaders(callable $callback)
 * @method static PendingRequest onStats(callable $callback)
 * @method static PendingRequest progress(callable $callback)
 * @method static PendingRequest retry(int $times, int $sleep = 0, ?callable $when = null)
 * @method static PendingRequest withBasicAuth(string $username, string $password)
 * @method static PendingRequest withBody(resource|string $content, string $contentType)
 * @method static PendingRequest withCookies(array $cookies, string $domain)
 * @method static PendingRequest withDigestAuth(string $username, string $password)
 * @method static PendingRequest withHeaders(array $headers)
 * @method static PendingRequest withMiddleware(callable $middleware)
 * @method static PendingRequest withOptions(array $options)
 * @method static PendingRequest withToken(string $token, string $type = 'Bearer')
 * @method static PendingRequest withUrlParameters(array $parameters = [])
 * @method static PendingRequest withUserAgent(string $userAgent)
 * @method static PendingRequest withoutRedirecting()
 * @method static PendingRequest withoutVerifying()
 * @method static PendingRequest throw(callable $callback = null)
 * @method static PendingRequest throwIf(bool|callable $condition, callable|null $throwCallback)
 * @method static PendingRequest throwUnless($condition)
 * @method static Response throwIfStatus(callable|int $statusCode)
 * @method static Response throwUnlessStatus(callable|int $statusCode)
 * @method static Response throwIfClientError()
 * @method static Response throwIfServerError()
 * @method static array pool(callable $callback)
 * @method static Response delete(string $url, array $data = [])
 * @method static Response get(string $url, array|string|null $query = null)
 * @method static Response head(string $url, array|string|null $query = null)
 * @method static Response patch(string $url, array $data = [])
 * @method static Response post(string $url, array $data = [])
 * @method static Response put(string $url, array $data = [])
 * @method static Response send(string $method, string $url, array $options = [])
 * @method static ResponseSequence fakeSequence(string $urlPattern = '*')
 * @method static void assertSent(callable $callback)
 * @method static void assertSentInOrder(array $callbacks)
 * @method static void assertNotSent(callable $callback)
 * @method static void assertNothingSent()
 * @method static void assertSentCount(int $count)
 * @method static void assertSequencesAreEmpty()
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

    public static function __callStatic($name, $arguments)
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
