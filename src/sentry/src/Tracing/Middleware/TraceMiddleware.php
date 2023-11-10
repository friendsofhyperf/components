<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Tracing\Middleware;

use Closure;
use FriendsOfHyperf\Sentry\Switcher;
use FriendsOfHyperf\Sentry\Tracing\SpanStarter;
use FriendsOfHyperf\Sentry\Tracing\TagManager;
use Hyperf\HttpServer\Router\Dispatched;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanStatus;
use Sentry\Tracing\Transaction;
use Sentry\Tracing\TransactionSource;
use Throwable;

use function Hyperf\Coroutine\defer;

class TraceMiddleware implements MiddlewareInterface
{
    use SpanStarter;

    /**
     * @var string route / url / custom
     */
    protected string $source = 'route';

    public function __construct(
        protected ContainerInterface $container,
        protected Switcher $switcher,
        protected TagManager $tagManager
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): PsrResponseInterface
    {
        /** @var Dispatched|null $dispatched */
        $dispatched = $request->getAttribute(Dispatched::class);

        // Must be called before all.
        $transaction = $this->startTransaction(
            $request,
            $dispatched?->serverName ?? 'http'
        );

        if (
            ! $dispatched?->isFound()
            && ! $this->switcher->isTracingEnable('missing_routes')
            // If this transaction is not sampled, we can stop here to prevent doing work for nothing
            && (! $transaction || ! $transaction->getSampled())
        ) {
            return $handler->handle($request);
        }

        try {
            $response = $handler->handle($request);

            // Set http status code
            $transaction->setHttpStatus($response->getStatusCode());

            // Append sentry-trace header to response
            $traceId = (string) $transaction->getTraceId();
            if ($traceId) {
                $response = $response->withHeader('sentry-trace-id', $traceId);
            }
        } catch (Throwable $exception) {
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

            throw $exception;
        }

        return $response;
    }

    private function startTransaction(ServerRequestInterface $request, ?string $server = null): ?Transaction
    {
        $server ??= 'http';
        $path = $request->getUri()->getPath();
        /**
         * @var string $route
         * @var array $routeParams
         * @var string $routeCallback
         */
        [$route, $routeParams, $routeCallback] = $this->getRouteInfo($request);

        /**
         * @var string $name
         * @var TransactionSource $source
         */
        [$name, $source] = match (strtolower($this->source)) {
            'custom' => [$routeCallback, TransactionSource::custom()],
            'url' => [$path, TransactionSource::url()],
            default => [$route, TransactionSource::route()],
        };

        // Get sentry-trace and baggage
        $transaction = $this->startRequestTransaction(
            $request,
            name: $name,
            op: sprintf('%s.server', $server),
            description: sprintf('request: %s %s', $request->getMethod(), $path),
            source: $source,
        );

        // Set data
        $data = [
            'url' => $path,
            'http.request.method' => strtoupper($request->getMethod()),
        ];
        $tags = [];

        if ($this->tagManager->has('request.route.params') && $routeParams) {
            $data[$this->tagManager->get('request.route.params')] = $routeParams;
        }
        if ($this->tagManager->has('request.query_params') && $queryParams = $request->getQueryParams()) {
            $data[$this->tagManager->get('request.query_params')] = $queryParams;
        }
        if ($this->tagManager->has('request.body') && $parsedBody = $request->getParsedBody()) {
            $data[$this->tagManager->get('request.body')] = $parsedBody;
        }

        // Set tags
        if ($this->tagManager->has('request.http.path')) {
            $tags[$this->tagManager->get('request.http.path')] = $path;
        }
        if ($this->tagManager->has('request.http.method')) {
            $tags[$this->tagManager->get('request.http.method')] = strtoupper($request->getMethod());
        }
        if ($this->tagManager->has('request.route.callback') && $routeCallback) {
            $tags[$this->tagManager->get('request.route.callback')] = $routeCallback;
        }
        if ($this->tagManager->has('request.header')) {
            foreach ($request->getHeaders() as $key => $value) {
                $tags[$this->tagManager->get('request.header') . '.' . $key] = implode(', ', $value);
            }
        }

        $transaction->setData($data);
        $transaction->setTags($tags);

        $span = $this->startSpan(
            'request.received',
            'request.received',
        );

        defer(function () use ($transaction, $span) {
            $span->finish();

            SentrySdk::getCurrentHub()->setSpan($transaction);
            $transaction->finish();
        });

        return $transaction;
    }

    private function getRouteInfo(ServerRequestInterface $request): array
    {
        /** @var Dispatched|null $dispatched */
        $dispatched = $request->getAttribute(Dispatched::class);
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
