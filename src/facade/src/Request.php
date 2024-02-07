<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Facade;

use Hyperf\Context\RequestContext;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * @method static array all()
 * @method static mixed query(?string $key = null, mixed $default = null)
 * @method static mixed post(?string $key = null, mixed $default = null)
 * @method static mixed input(string $key, mixed $default = null)
 * @method static array inputs(array $keys, array $default = null)
 * @method static mixed hasInput(array $keys)
 * @method static bool has(array|string $keys)
 * @method static null|string header(string $key, ?string $default = null)
 * @method static mixed route(string $key, mixed $default = null)
 * @method static string getPathInfo()
 * @method static mixed is(...$patterns)
 * @method static string decodedPath()
 * @method static string getRequestUri()
 * @method static string url()
 * @method static string fullUrl()
 * @method static null|string getQueryString()
 * @method static string normalizeQueryString(string $qs)
 * @method static mixed cookie(string $key, mixed $default = null);
 * @method static bool hasCookie(string $key)
 * @method static mixed server(string $key, mixed $default = null)
 * @method static bool isMethod(string $method)
 * @method static mixed file(string $key, mixed $default = null);
 * @method static bool hasFile(string $key)
 * @method static array allFiles()
 * @method static bool anyFilled($keys)
 * @method static bool boolean($key = null, $default = false)
 * @method static \Hyperf\Collection\Collection collect(array|string|null $key = null)
 * @method static null|\Carbon\Carbon date(string $key, ?string $format = null, ?string $tz = null)
 * @method static array except($keys)
 * @method static bool filled(array|string $key)
 * @method static bool hasAny(array|string $keys)
 * @method static bool isEmptyString(string $key)
 * @method static bool isNotFilled(array|string $key)
 * @method static array keys()
 * @method static string host()
 * @method static string httpHost()
 * @method static string schemeAndHttpHost()
 * @method static self merge(array $input)
 * @method static self mergeIfMissing(array $input)
 * @method static bool missing($key)
 * @method static array only($keys)
 * @method static self|mixed whenFilled(string $key, callable $callback, callable $default = null);
 * @method static self|mixed whenHas(string $key, callable $callback, callable $default = null);
 * @method static bool isJson()
 * @method static string getProtocolVersion()
 * @method static static setProtocolVersion(string $version)
 * @method static static withProtocolVersion(mixed $version)
 * @method static bool hasHeader(mixed $name)
 * @method static array getHeader(mixed $name)
 * @method static string getHeaderLine(mixed $name)
 * @method static static setHeader(string $name, mixed $value)
 * @method static static withHeader(mixed $name, mixed $value)
 * @method static static addHeader(string $name, mixed $value)
 * @method static static withAddedHeader(mixed $name, mixed $value)
 * @method static static unsetHeader(string $name)
 * @method static static withoutHeader(mixed $name)
 * @method static array getHeaders()
 * @method static array getStandardHeaders()
 * @method static static setHeaders(array $headers)
 * @method static static withHeaders(array $headers)
 * @method static bool shouldKeepAlive()
 * @method static StreamInterface getBody()
 * @method static static setBody(StreamInterface $body)
 * @method static static withBody(StreamInterface $body)
 * @method static string toString(bool $withoutBody = false)
 * @method static string getMethod()
 * @method static static setMethod(string $method)
 * @method static static withMethod(mixed $method)
 * @method static UriInterface getUri()
 * @method static static setUri(UriInterface|string $uri, ?bool $preserveHost = null)
 * @method static static withUri(UriInterface $uri, $preserveHost = null)
 * @method static string getRequestTarget()
 * @method static static setRequestTarget(string $requestTarget)
 * @method static static withRequestTarget(mixed $requestTarget)
 * @see \Swow\Psr7\Message\RequestPlusInterface
 */
class Request
{
    public static function __callStatic($name, $arguments)
    {
        if (str_starts_with($name, 'with')) {
            return RequestContext::set(
                RequestContext::get()->{$name}(...$arguments)
            );
        }

        return RequestContext::get()->{$name}(...$arguments);
    }
}
