<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use Hyperf\Context\Context;
use Hyperf\HttpMessage\Uri\Uri;
use Hyperf\HttpServer\Request;
use Mockery as m;
use Psr\Http\Message\ServerRequestInterface;
use Swow\Psr7\Message\ServerRequestPlusInterface;

afterEach(function () {
    Context::set(ServerRequestInterface::class, null);
    Context::set('http.request.parsedData', null);
});

test('test only', function () {
    $psrRequest = m::mock(ServerRequestPlusInterface::class, [
        'getParsedBody' => ['id' => 1],
        'getQueryParams' => [],
    ]);
    Context::set(ServerRequestInterface::class, $psrRequest);

    $request = new Request();

    expect($request->input('id'))->toBe(1);
    expect($request->only(['id']))->toBe(['id' => 1]);
});

test('test isEmptyString', function () {
    $psrRequest = m::mock(ServerRequestPlusInterface::class, [
        'getParsedBody' => ['id' => 1],
        'getQueryParams' => [],
    ]);
    Context::set(ServerRequestInterface::class, $psrRequest);

    $request = new Request();

    expect($request->isEmptyString('foo'))->toBeTrue();
    expect($request->isEmptyString('id'))->toBeFalse();
});

test('test getHost', function () {
    $request = new Request();

    $host = 'foo.com';
    $psrRequest = m::mock(ServerRequestPlusInterface::class, function ($mock) use ($host) {
        $mock->shouldReceive('getHeader')->with('HOST')->andReturn([$host]);
    });
    Context::set(ServerRequestInterface::class, $psrRequest);

    expect($request->getHost())->toBe($host);

    $psrRequest = m::mock(ServerRequestPlusInterface::class, function ($mock) use ($host) {
        $mock->shouldReceive('getHeader')->with('HOST')->andReturn([]);
        $mock->shouldReceive('getServerParams')->with('SERVER_NAME')->andReturn([$host]);
    });
    Context::set(ServerRequestInterface::class, $psrRequest);

    expect($request->getHost())->toBe($host);

    $psrRequest = m::mock(ServerRequestPlusInterface::class, function ($mock) use ($host) {
        $mock->shouldReceive('getHeader')->with('HOST')->andReturn([]);
        $mock->shouldReceive('getServerParams')->with('SERVER_NAME')->andReturn([]);
        $mock->shouldReceive('getServerParams')->with('SERVER_ADDR')->andReturn([$host]);
    });
    Context::set(ServerRequestInterface::class, $psrRequest);

    expect($request->getHost())->toBe($host);
});

test('test getPort', function () {
    $request = new Request();

    $port = 80;
    $psrRequest = m::mock(ServerRequestPlusInterface::class, function ($mock) use ($port) {
        $mock->shouldReceive('getHeader')->with('HOST')->andReturn([]);
        $mock->shouldReceive('getServerParams')->with('SERVER_PORT')->andReturn([$port]);
    });
    Context::set(ServerRequestInterface::class, $psrRequest);

    expect($request->getPort())->toBe($port);
    $port = 80;
    $psrRequest = m::mock(ServerRequestPlusInterface::class, function ($mock) {
        $mock->shouldReceive('getHeader')->with('HOST')->andReturn(['foo.com:80']);
    });
    Context::set(ServerRequestInterface::class, $psrRequest);

    expect($request->getPort())->toBe($port);

    $port = 80;
    $psrRequest = m::mock(ServerRequestPlusInterface::class, function ($mock) {
        $mock->shouldReceive('getHeader')->with('HOST')->andReturn(['foo.com']);
        $mock->shouldReceive('getServerParams')->with('HTTPS')->andReturn(['off']);
    });
    Context::set(ServerRequestInterface::class, $psrRequest);

    expect($request->getPort())->toBe($port);

    $port = 443;
    $psrRequest = m::mock(ServerRequestPlusInterface::class, function ($mock) {
        $mock->shouldReceive('getHeader')->with('HOST')->andReturn(['foo.com']);
        $mock->shouldReceive('getServerParams')->with('HTTPS')->andReturn(['on']);
    });
    Context::set(ServerRequestInterface::class, $psrRequest);

    expect($request->getPort())->toBe($port);
});

test('test getScheme', function () {
    $request = new Request();

    $psrRequest = m::mock(ServerRequestPlusInterface::class, function ($mock) {
        $mock->shouldReceive('getServerParams')->with('HTTPS')->andReturn(['on']);
    });
    Context::set(ServerRequestInterface::class, $psrRequest);

    expect($request->getScheme())->toBe('https');

    $psrRequest = m::mock(ServerRequestPlusInterface::class, function ($mock) {
        $mock->shouldReceive('getServerParams')->with('HTTPS')->andReturn(['off']);
    });
    Context::set(ServerRequestInterface::class, $psrRequest);

    expect($request->getScheme())->toBe('http');
});

test('test wantsJson', function () {
    $request = new Request();

    $psrRequest = m::mock(ServerRequestPlusInterface::class, function ($mock) {
        $mock->shouldReceive('hasHeader')->with('ACCEPT')->andReturn(true);
        $mock->shouldReceive('getHeaderLine')->with('ACCEPT')->andReturn('application/json');
    });
    Context::set(ServerRequestInterface::class, $psrRequest);

    expect($request->wantsJson())->toBeTrue();

    $psrRequest = m::mock(ServerRequestPlusInterface::class, function ($mock) {
        $mock->shouldReceive('hasHeader')->with('ACCEPT')->andReturn(true);
        $mock->shouldReceive('getHeaderLine')->with('ACCEPT')->andReturn('text/html');
    });
    Context::set(ServerRequestInterface::class, $psrRequest);

    expect($request->wantsJson())->toBeFalse();
});

test('test fake', function () {
    $psrRequest = Request::fake();
    expect($psrRequest)->toBeInstanceOf(ServerRequestInterface::class);

    $psrRequest = Request::fake(function ($request) {
        return $request->withMethod('POST')->withUri(new Uri('/foo'));
    });

    expect($psrRequest->getMethod())->toBe('POST');
    expect($psrRequest->getUri()->getPath())->toBe('/foo');

    Context::set(ServerRequestInterface::class, $psrRequest);

    $request = new Request();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/foo');
});

test('test getPsrRequest', function () {
    $psrRequest = Request::fake();
    Context::set(ServerRequestInterface::class, $psrRequest);

    $request = new Request();
    expect($request->getPsrRequest())->toBe($psrRequest);
});
