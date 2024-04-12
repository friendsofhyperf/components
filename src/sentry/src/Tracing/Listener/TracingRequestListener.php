<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Tracing\Listener;

use Closure;
use FriendsOfHyperf\Sentry\Switcher;
use FriendsOfHyperf\Sentry\Tracing\SpanStarter;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\HttpServer\Event\RequestHandled;
use Hyperf\HttpServer\Event\RequestReceived;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Rpc\Context as RpcContext;
use Hyperf\RpcServer\Event\RequestHandled as RpcRequestHandled;
use Hyperf\RpcServer\Event\RequestReceived as RpcRequestReceived;
use Psr\Container\ContainerInterface;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanStatus;
use Sentry\Tracing\TransactionSource;
use Swow\Psr7\Message\ResponsePlusInterface;

use function Hyperf\Coroutine\defer;

class TracingRequestListener implements ListenerInterface
{
    use SpanStarter;

    /**
     * @var string route / url / custom
     */
    protected string $source = 'route';

    public function __construct(
        protected ContainerInterface $container,
        protected Switcher $switcher
    ) {
    }

    public function listen(): array
    {
        return [
            RequestReceived::class,
            RequestHandled::class,
            RpcRequestReceived::class,
            RpcRequestHandled::class,
        ];
    }

    public function process(object $event): void
    {
        if (! $this->switcher->isTracingEnable('request')) {
            return;
        }

        match ($event::class) {
            RequestReceived::class, RpcRequestReceived::class => $this->startTransaction($event),
            RequestHandled::class, RpcRequestHandled::class => $this->setTraceIdAndException($event),
            default => null,
        };
    }

    private function startTransaction(RequestReceived|RpcRequestReceived $event): void
    {
        $request = $event->request;
        /** @var Dispatched $dispatched */
        $dispatched = $request->getAttribute(Dispatched::class);

        if (! $dispatched->isFound() && ! $this->switcher->isTracingEnable('missing_routes')) {
            return;
        }

        $serverName = $dispatched->serverName ?? 'http';
        $path = $request->getUri()->getPath();
        $method = strtoupper($request->getMethod());

        /**
         * @var string $route
         * @var array $routeParams
         * @var string $routeCallback
         */
        [$route, $routeParams, $routeCallback] = $this->parseRoute($dispatched);

        /**
         * @var string $name
         * @var TransactionSource $source
         */
        [$name, $source] = match (strtolower($this->source)) {
            'custom' => [$routeCallback, TransactionSource::custom()],
            'url' => [$method . ' ' . $path, TransactionSource::url()],
            default => [$method . ' ' . $route, TransactionSource::route()],
        };

        // Get sentry-trace and baggage
        $transaction = $this->startRequestTransaction(
            $request,
            name: $name,
            op: sprintf('%s.server', $serverName),
            description: sprintf('request: %s %s', $method, $path),
            source: $source,
        );

        if (! $transaction->getSampled()) {
            return;
        }

        // Set data
        $data = [
            'url.scheme' => $request->getUri()->getScheme(),
            'url.path' => $path,
            'http.request.method' => $method,
            'http.route' => $route,
            'http.route.params' => $routeParams,
        ];
        foreach ($request->getHeaders() as $key => $value) {
            $data['http.request.header.' . $key] = implode(', ', $value);
        }
        if ($this->container->has(RpcContext::class)) {
            $data['rpc.context'] = $this->container->get(RpcContext::class)->getData();
        }

        $transaction->setData($data);

        $span = $this->startSpan('request.received', 'request.received', true);

        defer(function () use ($transaction, $span) {
            $span->finish();

            SentrySdk::getCurrentHub()->setSpan($transaction);
            $transaction->finish();
        });
    }

    private function setTraceIdAndException(RequestHandled|RpcRequestHandled $event): void
    {
        $transaction = SentrySdk::getCurrentHub()->getTransaction();

        if (
            ! $transaction
            || ! $transaction->getSampled()
            || ! $traceId = (string) $transaction->getTraceId()
        ) {
            return;
        }

        if ($event instanceof RpcRequestHandled) {
            $this->container->has(RpcContext::class) && $this->container->get(RpcContext::class)->set('sentry-trace-id', $traceId);
        } elseif ($event->response instanceof ResponsePlusInterface) {
            $event->response->setHeader('sentry-trace-id', $traceId);
        }

        // Set http status code
        $transaction->setHttpStatus($event->response->getStatusCode());

        if ($exception = $event->getThrowable()) {
            $transaction->setStatus(SpanStatus::internalError());
            $transaction->setTags([
                'error' => true,
                'exception.class' => $exception::class,
                'exception.code' => $exception->getCode(),
                'exception.message' => $exception->getMessage(),
            ]);
            $transaction->setData([
                'exception.stack_trace' => (string) $exception,
            ]);
        }
    }

    private function parseRoute(Dispatched $dispatched): array
    {
        $route = '<missing route>';
        $params = [];
        $callback = '';

        if ($dispatched instanceof Dispatched && $dispatched->isFound()) {
            $route = $dispatched->handler->route;
            $params = $dispatched->params;
            $callback = match (true) {
                $dispatched->handler->callback instanceof Closure => 'closure',
                is_array($dispatched->handler->callback) => implode('@', $dispatched->handler->callback),
                is_string($dispatched->handler->callback) => $dispatched->handler->callback,
                default => $callback,
            };
        }

        return [$route, $params, $callback];
    }
}
