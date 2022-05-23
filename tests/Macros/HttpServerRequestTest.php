<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Tests\Macros;

use FriendsOfHyperf\Tests\TestCase;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Request;
use Mockery;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal
 * @coversNothing
 */
class HttpServerRequestTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        Context::set(ServerRequestInterface::class, null);
        Context::set('http.request.parsedData', null);
    }

    public function testOnly()
    {
        $psrRequest = Mockery::mock(ServerRequestInterface::class);
        $psrRequest->shouldReceive('getParsedBody')->andReturn(['id' => 1]);
        $psrRequest->shouldReceive('getQueryParams')->andReturn([]);
        Context::set(ServerRequestInterface::class, $psrRequest);

        $request = new Request();

        $this->assertSame(1, $request->input('id'));
        $this->assertSame(['id' => 1], $request->only(['id']));
    }

    public function testIsEmptyString()
    {
        $psrRequest = Mockery::mock(ServerRequestInterface::class);
        $psrRequest->shouldReceive('getParsedBody')->andReturn(['id' => 1]);
        $psrRequest->shouldReceive('getQueryParams')->andReturn([]);
        Context::set(ServerRequestInterface::class, $psrRequest);

        $request = new Request();

        $this->assertTrue($request->isEmptyString('foo'));
        $this->assertFalse($request->isEmptyString('id'));
    }
}
