<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
use Hyperf\Context\Context;
use Hyperf\HttpServer\Request;
use Mockery as m;
use Pest\Mock\Mock;
use Psr\Http\Message\ServerRequestInterface;

uses(\FriendsOfHyperf\Tests\TestCase::class)->group('macros', 'request');

afterEach(function () {
    m::close();
    Context::set(ServerRequestInterface::class, null);
    Context::set('http.request.parsedData', null);
});

test('test only', function () {
    $psrRequest = (new Mock(ServerRequestInterface::class))->expect(
        getParsedBody: fn () => ['id' => 1],
        getQueryParams: fn () => [],
    );
    Context::set(ServerRequestInterface::class, $psrRequest);

    $request = new Request();

    expect($request->input('id'))->toBe(1);
    expect($request->only(['id']))->toBe(['id' => 1]);
});

test('test isEmptyString', function () {
    $psrRequest = (new Mock(ServerRequestInterface::class))->expect(
        getParsedBody: fn () => ['id' => 1],
        getQueryParams: fn () => [],
    );
    Context::set(ServerRequestInterface::class, $psrRequest);

    $request = new Request();

    expect($request->isEmptyString('foo'))->toBeTrue();
    expect($request->isEmptyString('id'))->toBeFalse();
});
