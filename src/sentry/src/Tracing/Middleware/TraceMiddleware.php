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

use FriendsOfHyperf\Sentry\Switcher;
use FriendsOfHyperf\Sentry\Tracing\TraceContext;
use Hyperf\Contract\ConfigInterface;
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
    public function __construct(
        protected ContainerInterface $container,
        protected ConfigInterface $config,
        protected Switcher $switcher
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): PsrResponseInterface
    {
        $this->startTransaction($request, SentrySdk::getCurrentHub());

        $transaction = TraceContext::getTransaction();

        defer(function () {
            try {
                $this->finishTransaction();
            } catch (Throwable) {
            }
        });

        try {
            $response = $handler->handle($request);

            TraceContext::getTransaction()?->setHttpStatus($response->getStatusCode());
            $traceId = (string) TraceContext::getTransaction()?->getTraceId();
            if ($traceId) {
                $response = $response->withHeader('sentry-trace', $traceId);
            }
        } catch (Throwable $exception) {
            $transaction->setStatus(SpanStatus::internalError());
            if (! $this->switcher->isExceptionIgnored($exception)) {
                $transaction->setTags([
                    'exception.class' => get_class($exception),
                    'exception.code' => $exception->getCode(),
                    'exception.message' => $exception->getMessage(),
                    'exception.stack_trace' => (string) $exception,
                ]);
            }

            throw $exception;
        } finally {
            TraceContext::getSpan()?->finish();
        }

        return $response;
    }

    private function startTransaction(ServerRequestInterface $request, HubInterface $sentry, string $server = 'http'): void
    {
        $startTimestamp = microtime(true);
        $path = $request->getUri()->getPath();
        $context = continueTrace(
            $request->getHeaderLine('sentry-trace', ''),
            $request->getHeaderLine('baggage', '')
        );

        $context->setOp($server . '.server');
        // $context->setDescription(sprintf('request: %s %s', $request->getMethod(), $requestPath));
        $context->setName(sprintf('request: %s %s', $request->getMethod(), $path));
        $context->setSource(TransactionSource::url());
        $context->setStartTimestamp($startTimestamp);

        $context->setData([
            'url' => $path,
            'http.method' => strtoupper($request->getMethod()),
        ]);

        $transaction = $sentry->startTransaction($context);

        // If this transaction is not sampled, we can stop here to prevent doing work for nothing
        if (! $transaction->getSampled()) {
            return;
        }

        TraceContext::setTransaction($transaction);
        TraceContext::setWaitGroup();

        $sentry->setSpan($transaction);

        $reqContext = new SpanContext();
        $reqContext->setOp('request.received');
        $reqContext->setDescription(
            sprintf('request: %s %s', $request->getMethod(), $path)
        );
        $reqContext->setStartTimestamp(microtime(true));

        $reqSpan = $transaction->startChild($reqContext);
        TraceContext::setSpan($reqSpan);

        $sentry->setSpan($reqSpan);
    }

    private function finishTransaction(): void
    {
        if (! $transaction = TraceContext::getTransaction()) {
            return;
        }

        TraceContext::getWaitGroup()?->wait(
            (int) $this->config->get('sentry.tracing_wait_timeout', 10)
        );

        SentrySdk::getCurrentHub()->setSpan($transaction);
        $transaction->finish();

        TraceContext::clearTransaction();
        TraceContext::clearSpan();
    }
}
