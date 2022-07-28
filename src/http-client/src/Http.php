<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Http\Client;

use Hyperf\Macroable\Macroable;

/**
 * @method static \GuzzleHttp\Promise\PromiseInterface response($body = null, $status = 200, $headers = [])
 * @method static Factory fake($callback = null)
 * @method static PendingRequest accept(string $contentType)
 * @method static PendingRequest acceptJson()
 * @method static PendingRequest asForm()
 * @method static PendingRequest asJson()
 * @method static PendingRequest asMultipart()
 * @method static PendingRequest async()
 * @method static PendingRequest attach(array|string $name, string $contents = '', null|string $filename = null, array $headers = [])
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
 * @method static PendingRequest withUserAgent(string $userAgent)
 * @method static PendingRequest withoutRedirecting()
 * @method static PendingRequest withoutVerifying()
 * @method PendingRequest throw(callable $callback = null)
 * @method PendingRequest throwIf($condition)
 * @method PendingRequest throwUnless($condition)
 * @method static array pool(callable $callback)
 * @method static Response delete(string $url, array $data = [])
 * @method static Response get(string $url, null|array|string $query = null)
 * @method static Response head(string $url, null|array|string $query = null)
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
    use Macroable {
        __call as macroCall;
    }

    public static function __callStatic($name, $arguments)
    {
        return make(Factory::class)->{$name}(...$arguments);
    }
}
