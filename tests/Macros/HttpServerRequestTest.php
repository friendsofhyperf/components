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
use Hyperf\HttpServer\Request;
use Mockery as m;
use Psr\Http\Message\ServerRequestInterface;
use Swow\Psr7\Message\ServerRequestPlusInterface;

uses()->group('macros', 'request');

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
