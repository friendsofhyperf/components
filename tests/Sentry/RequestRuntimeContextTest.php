<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Sentry;

use FastRoute\Dispatcher;
use FriendsOfHyperf\Sentry\Feature;
use FriendsOfHyperf\Sentry\Listener\EventHandleListener as BaseEventHandleListener;
use FriendsOfHyperf\Sentry\Tracing\Listener\EventHandleListener as TracingEventHandleListener;
use FriendsOfHyperf\Sentry\Tracing\Tracer;
use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\HttpServer\Event\RequestReceived as HttpRequestReceived;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\HttpServer\Router\Handler;
use Hyperf\Rpc\Context as RpcContext;
use Mockery as m;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Sentry\SentrySdk;
use Swoole\Coroutine;

beforeEach(function () {
    // Tests run inside a Swoole coroutine (TestCase::runBare wraps each test in
    // Swoole\Coroutine\run), so Hyperf\Coroutine\defer() registers a coroutine-exit callback.
    // Make sure no active runtime context leaks from a previous test.
    SentrySdk::endContext();

    $this->container = m::mock(ContainerInterface::class);
    $this->container->shouldReceive('has')->with(RpcContext::class)->andReturn(false);

    // The startTransaction() and Carrier helpers resolve from the ApplicationContext
    // container, so make Tracer resolvable there deterministically.
    $this->container->shouldReceive('get')->with(Tracer::class)->andReturn(new Tracer());
    ApplicationContext::setContainer($this->container);

    $this->makeRequestReceived = function (): HttpRequestReceived {
        $handler = new Handler('App\Controller\IndexController::index', '/test');
        $dispatched = new Dispatched([Dispatcher::FOUND, $handler, []], 'http');

        $uri = m::mock(UriInterface::class);
        $uri->shouldReceive('getPath')->andReturn('/test');
        $uri->shouldReceive('getScheme')->andReturn('http');

        $request = m::mock(ServerRequestInterface::class);
        $request->shouldReceive('getAttribute')->with(Dispatched::class)->andReturn($dispatched);
        $request->shouldReceive('getUri')->andReturn($uri);
        $request->shouldReceive('getMethod')->andReturn('GET');
        $request->shouldReceive('getHeaders')->andReturn([]);
        $request->shouldReceive('hasHeader')->andReturn(false);
        $request->shouldReceive('getHeaderLine')->andReturn('');

        $response = m::mock(ResponseInterface::class);

        return new HttpRequestReceived($request, $response);
    };
});

afterEach(function () {
    SentrySdk::endContext();
    m::close();
});

test('tracing listener starts an isolated runtime context per request and restores on endContext', function () {
    $config = new Config([
        'sentry' => [
            'enable' => ['request' => true],
            'tracing' => ['request' => true, 'missing_routes' => true],
        ],
    ]);
    $feature = new Feature($config);
    $listener = new TracingEventHandleListener($this->container, $config, $feature);

    $before = SentrySdk::getCurrentHub();
    $listener->process(($this->makeRequestReceived)());
    $after = SentrySdk::getCurrentHub();

    // The request got its own hub instead of the shared global one.
    expect($after)->not->toBe($before);

    // Ending the context manually restores the global hub instance.
    SentrySdk::endContext();
    expect(SentrySdk::getCurrentHub())->toBe($before);
});

test('the deferred endContext restores the global hub when the request coroutine exits', function () {
    $config = new Config([
        'sentry' => [
            'enable' => ['request' => true],
            'tracing' => ['request' => true, 'missing_routes' => true],
        ],
    ]);
    $feature = new Feature($config);
    $listener = new TracingEventHandleListener($this->container, $config, $feature);
    $event = ($this->makeRequestReceived)();

    $before = SentrySdk::getCurrentHub();
    $innerHub = null;

    $cid = Coroutine::create(function () use ($listener, $event, &$innerHub): void {
        $listener->process($event);
        $innerHub = SentrySdk::getCurrentHub();
    });
    Coroutine::join([$cid]);

    // While the request coroutine is alive it has an isolated hub...
    expect($innerHub)->not->toBe($before);
    // ...and once it exits, the defer ends the context automatically.
    expect(SentrySdk::getCurrentHub())->toBe($before);
});

test('base listener starts an isolated runtime context when tracing is disabled', function () {
    $config = new Config([
        'sentry' => [
            'enable' => ['request' => true],
            'tracing' => ['request' => false],
        ],
    ]);
    $feature = new Feature($config);
    $listener = new BaseEventHandleListener($this->container, $feature, $config, m::mock(StdoutLoggerInterface::class));

    $before = SentrySdk::getCurrentHub();
    $listener->process(($this->makeRequestReceived)());

    expect(SentrySdk::getCurrentHub())->not->toBe($before);

    SentrySdk::endContext();
    expect(SentrySdk::getCurrentHub())->toBe($before);
});

test('no runtime context is started when request features are disabled', function () {
    $config = new Config([
        'sentry' => [
            'enable' => ['request' => false],
            'tracing' => ['request' => false],
        ],
    ]);
    $feature = new Feature($config);
    $tracingListener = new TracingEventHandleListener($this->container, $config, $feature);
    $baseListener = new BaseEventHandleListener($this->container, $feature, $config, m::mock(StdoutLoggerInterface::class));

    $before = SentrySdk::getCurrentHub();
    $tracingListener->process(($this->makeRequestReceived)());
    $baseListener->process(($this->makeRequestReceived)());

    expect(SentrySdk::getCurrentHub())->toBe($before);
});
