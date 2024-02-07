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

use Hyperf\Context\ResponseContext;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @method static string getProtocolVersion()
 * @method static MessageInterface withProtocolVersion(string $version)
 * @method static array getHeaders()
 * @method static bool hasHeader(string $name)
 * @method static array getHeader(string $name)
 * @method static string getHeaderLine(string $name)
 * @method static MessageInterface withHeader(string $name, $value)
 * @method static MessageInterface withAddedHeader(string $name, $value)
 * @method static MessageInterface withoutHeader(string $name)
 * @method static MessageInterface withBody(StreamInterface $body)
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
 * @method static int getStatusCode()
 * @method static string getReasonPhrase()
 * @method static static setStatus(int $code, string $reasonPhrase = '')
 * @method static static withStatus($code, $reasonPhrase = '')
 * @see \Swow\Psr7\Message\ResponsePlusInterface
 */
class Response
{
    public static function __callStatic($name, $arguments)
    {
        if (str_starts_with($name, 'with')) {
            return ResponseContext::set(
                ResponseContext::get()->{$name}(...$arguments)
            );
        }

        return ResponseContext::get()->{$name}(...$arguments);
    }
}
