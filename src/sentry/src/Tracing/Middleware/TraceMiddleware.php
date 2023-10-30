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
use FriendsOfHyperf\Sentry\SentryContext;
use FriendsOfHyperf\Sentry\Switcher;
use FriendsOfHyperf\Sentry\Tracing\TagManager;
use FriendsOfHyperf\Sentry\Tracing\TraceContext;
use Hyperf\Coroutine\Coroutine;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Rpc\Context as RpcContext;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sentry\SentrySdk;
use Sentry\State\HubInterface;
use Sentry\Tracing\SpanContext;
use Sentry\Tracing\SpanStatus;
use Sentry\Tracing\Transaction;
use Sentry\Tracing\TransactionSource;
use Throwable;

use function Hyperf\Coroutine\defer;
use function Sentry\continueTrace;

class TraceMiddleware implements MiddlewareInterface
{
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
        // Must be called before all.
        $this->startTransaction($request, SentrySdk::getCurrentHub(), SentryContext::getServerName());

        /** @var Dispatched|null $dispatched */
        $dispatched = $request->getAttribute(Dispatched::class);
        if (
            ! $dispatched?->isFound()
            && ! $this->switcher->isTracingEnable('missing_routes', false)
        ) {
            return $handler->handle($request);
        }

        $transaction = TraceContext::getTransaction();

        if (! $transaction) {
            return $handler->handle($request);
        }

        defer(function () {
            try {
                $this->finishTransaction();
            } catch (Throwable) {
            }
        });

        try {
            $response = $handler->handle($request);

            // Set http status code
            $transaction->setHttpStatus($response->getStatusCode());
            // Set status
            $transaction->setStatus(SpanStatus::ok());

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
        } finally {
            TraceContext::getSpan()?->finish();
        }

        return $response;
    }

    private function startTransaction(ServerRequestInterface $request, HubInterface $sentry, ?string $server = null): void
    {
        $server ??= 'http';
        $startTimestamp = microtime(true);
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
            'route' => [$route, TransactionSource::route()],
            default => [$route, TransactionSource::route()],
        };

        // Get sentry-trace and baggage
        $sentryTrace = $request->getHeaderLine('sentry-trace', '');
        $baggage = $request->getHeaderLine('baggage', '');

        if ($this->container->has(RpcContext::class)) {
            $rpcContext = $this->container->get(RpcContext::class);
            $carrier = $rpcContext->get(TraceContext::RPC_CARRIER);
            if (! empty($carrier['sentry-trace']) && ! empty($carrier['baggage'])) {
                $sentryTrace = $carrier['sentry-trace'];
                $baggage = $carrier['baggage'];
            }
        }

        $context = continueTrace($sentryTrace, $baggage);
        $context->setName($name);
        $context->setSource($source);
        $context->setOp(sprintf('%s.server', $server));
        $context->setDescription(sprintf('request: %s %s', $request->getMethod(), $path));
        $context->setStartTimestamp($startTimestamp);

        // Set data
        $data = [
            'url' => $path,
            'http.request.method' => strtoupper($request->getMethod()),
        ];
        if ($this->tagManager->has('request.route.params') && $routeParams) {
            $data[$this->tagManager->get('request.route.params')] = $routeParams;
        }
        if ($this->tagManager->has('request.query_params') && $queryParams = $request->getQueryParams()) {
            $data[$this->tagManager->get('request.query_params')] = $queryParams;
        }
        if ($this->tagManager->has('request.body') && $parsedBody = $request->getParsedBody()) {
            $data[$this->tagManager->get('request.body')] = $parsedBody;
        }
        $context->setData($data);

        // Set tags
        $tags = [];
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
        $context->setTags($tags);

        // Start transaction
        $transaction = $sentry->startTransaction($context);

        // If this transaction is not sampled, we can stop here to prevent doing work for nothing
        if (! $transaction->getSampled()) {
            return;
        }

        // Set transaction to context
        TraceContext::setTransaction($transaction);

        $sentry->setSpan($transaction);

        $requestContext = new SpanContext();
        $requestContext->setOp('request.received');
        // $reqContext->setDescription('#' . Coroutine::id());
        $requestContext->setStartTimestamp(microtime(true));

        $requestSpan = $transaction->startChild($requestContext);
        TraceContext::setSpan($requestSpan);

        $sentry->setSpan($requestSpan);
    }

    private function finishTransaction(): void
    {
        if (! $transaction = TraceContext::getTransaction()) {
            return;
        }

        SentrySdk::getCurrentHub()->setSpan($transaction);
        TraceContext::getSpan()?->finish();
        $transaction->finish();

        TraceContext::clearTransaction();
        TraceContext::clearSpan();
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
