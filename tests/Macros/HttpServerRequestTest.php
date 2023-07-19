<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
use Hyperf\Context\Context;
use Hyperf\HttpServer\Request;
use Mockery as m;
use Psr\Http\Message\ServerRequestInterface;

uses()->group('macros', 'request');

afterEach(function () {
    m::close();
    Context::set(ServerRequestInterface::class, null);
    Context::set('http.request.parsedData', null);
});

test('test only', function () {
    $psrRequest = mocking(ServerRequestInterface::class)->expect(
        getParsedBody: fn () => ['id' => 1],
        getQueryParams: fn () => [],
    );
    Context::set(ServerRequestInterface::class, $psrRequest);

    $request = new Request();

    expect($request->input('id'))->toBe(1);
    expect($request->only(['id']))->toBe(['id' => 1]);
});

test('test isEmptyString', function () {
    $psrRequest = mocking(ServerRequestInterface::class)->expect(
        getParsedBody: fn () => ['id' => 1],
        getQueryParams: fn () => [],
    );
    Context::set(ServerRequestInterface::class, $psrRequest);

    $request = new Request();

    expect($request->isEmptyString('foo'))->toBeTrue();
    expect($request->isEmptyString('id'))->toBeFalse();
});

test('test get host', function () {
    $request = new Request();

    $host = 'foo.com';
    $psrRequest = m::mock(ServerRequestInterface::class, function ($mock) use ($host) {
        $mock->shouldReceive('getHeader')->with('HOST')->andReturn([$host]);
    });
    Context::set(ServerRequestInterface::class, $psrRequest);

    expect($request->getHost())->toBe($host);

    $psrRequest = m::mock(ServerRequestInterface::class, function ($mock) use ($host) {
        $mock->shouldReceive('getHeader')->with('HOST')->andReturn([]);
        $mock->shouldReceive('getServerParams')->with('SERVER_NAME')->andReturn([$host]);
    });
    Context::set(ServerRequestInterface::class, $psrRequest);

    expect($request->getHost())->toBe($host);

    $psrRequest = m::mock(ServerRequestInterface::class, function ($mock) use ($host) {
        $mock->shouldReceive('getHeader')->with('HOST')->andReturn([]);
        $mock->shouldReceive('getServerParams')->with('SERVER_NAME')->andReturn([]);
        $mock->shouldReceive('getServerParams')->with('SERVER_ADDR')->andReturn([$host]);
    });
    Context::set(ServerRequestInterface::class, $psrRequest);

    expect($request->getHost())->toBe($host);
});

test('test get port', function () {
    $request = new Request();

    $port = 80;
    $psrRequest = m::mock(ServerRequestInterface::class, function ($mock) use ($port) {
        $mock->shouldReceive('getHeader')->with('HOST')->andReturn([]);
        $mock->shouldReceive('getServerParams')->with('SERVER_PORT')->andReturn([$port]);
    });
    Context::set(ServerRequestInterface::class, $psrRequest);

    expect($request->getPort())->toBe($port);
    $port = 80;
    $psrRequest = m::mock(ServerRequestInterface::class, function ($mock) {
        $mock->shouldReceive('getHeader')->with('HOST')->andReturn(['foo.com:80']);
    });
    Context::set(ServerRequestInterface::class, $psrRequest);

    expect($request->getPort())->toBe($port);

    $port = 80;
    $psrRequest = m::mock(ServerRequestInterface::class, function ($mock) {
        $mock->shouldReceive('getHeader')->with('HOST')->andReturn(['foo.com']);
        $mock->shouldReceive('getServerParams')->with('HTTPS')->andReturn(['off']);
    });
    Context::set(ServerRequestInterface::class, $psrRequest);

    expect($request->getPort())->toBe($port);

    $port = 443;
    $psrRequest = m::mock(ServerRequestInterface::class, function ($mock) {
        $mock->shouldReceive('getHeader')->with('HOST')->andReturn(['foo.com']);
        $mock->shouldReceive('getServerParams')->with('HTTPS')->andReturn(['on']);
    });
    Context::set(ServerRequestInterface::class, $psrRequest);

    expect($request->getPort())->toBe($port);
});

test('test get scheme', function () {
    $request = new Request();

    $psrRequest = m::mock(ServerRequestInterface::class, function ($mock) {
        $mock->shouldReceive('getServerParams')->with('HTTPS')->andReturn(['on']);
    });
    Context::set(ServerRequestInterface::class, $psrRequest);

    expect($request->getScheme())->toBe('https');

    $psrRequest = m::mock(ServerRequestInterface::class, function ($mock) {
        $mock->shouldReceive('getServerParams')->with('HTTPS')->andReturn(['off']);
    });
    Context::set(ServerRequestInterface::class, $psrRequest);

    expect($request->getScheme())->toBe('http');
});
