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

use FriendsOfHyperf\Sentry\Switcher;
use FriendsOfHyperf\Sentry\Tracing\TraceContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpServer\Event;
use Hyperf\HttpServer\Event\RequestTerminated;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sentry\SentrySdk;
use Sentry\State\HubInterface;
use Sentry\Tracing\SpanContext;
use Sentry\Tracing\Transaction;
use Sentry\Tracing\TransactionSource;
use Swow\Psr7\Message\ResponsePlusInterface;

use function Sentry\continueTrace;

class TracingListener implements ListenerInterface
{
    public function __construct(
        protected ContainerInterface $container,
        protected ConfigInterface $config,
        protected Switcher $switcher
    ) {
    }

    public function listen(): array
    {
        return [
            Event\RequestReceived::class,
            Event\RequestHandled::class,
            Event\RequestTerminated::class,
        ];
    }

    /**
     * @param Event\RequestReceived|Event\RequestTerminated $event
     */
    public function process(object $event): void
    {
        $sentry = SentrySdk::getCurrentHub();

        match ($event::class) {
            Event\RequestTerminated::class => $this->finishTransaction($event),
            Event\RequestHandled::class => $this->handleTransaction($event),
            default => $this->startTransaction($event->request, $sentry, $event->server),
        };
    }

    private function startTransaction(ServerRequestInterface $request, HubInterface $sentry, string $server = 'http'): void
    {
        $requestStartTime = microtime(true);
        $requestPath = $request->getUri()->getPath();
        $context = continueTrace(
            $request->getHeaderLine('sentry-trace', ''),
            $request->getHeaderLine('baggage', '')
        );

        $context->setOp($server . '.server');
        // $context->setDescription(sprintf('request: %s %s', $request->getMethod(), $requestPath));
        $context->setName(sprintf('request: %s %s', $request->getMethod(), $requestPath));
        $context->setSource(TransactionSource::url());
        $context->setStartTimestamp($requestStartTime);

        $context->setData([
            'url' => $requestPath,
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
            sprintf('request: %s %s', $request->getMethod(), $requestPath)
        );
        $reqContext->setStartTimestamp(microtime(true));

        $reqSpan = $transaction->startChild($reqContext);
        TraceContext::setParent($reqSpan);

        $sentry->setSpan($reqSpan);
    }

    /**
     * @param Event\RequestHandled $event
     */
    private function handleTransaction(object $event): void
    {
        TraceContext::getParent()?->finish();

        /** @var ResponsePlusInterface|ResponseInterface $response */
        $response = $event->response;
        $traceId = (string) TraceContext::getTransaction()?->getTraceId();

        if (method_exists($response, 'addHeader')) {
            $response->addHeader('sentry-trace', $traceId);
        }
    }

    /**
     * @param RequestTerminated $event
     */
    private function finishTransaction(object $event): void
    {
        TraceContext::getWaitGroup()->wait(
            (int) $this->config->get('sentry.tracing_wait_timeout', 10)
        );

        $transaction = TraceContext::getTransaction();

        if ($transaction === null) {
            return;
        }

        $transaction->setHttpStatus($event->response->getStatusCode());

        if ($exception = $event->exception) {
            if (! $this->switcher->isExceptionIgnored($exception)) {
                $transaction->setTags([
                    'exception.class' => get_class($exception),
                    'exception.code' => $exception->getCode(),
                    'exception.message' => $exception->getMessage(),
                    'exception.stack_trace' => (string) $exception,
                ]);
            }
        }

        SentrySdk::getCurrentHub()->setSpan($transaction);
        $transaction->finish();

        TraceContext::clearTransaction();
        TraceContext::clearParent();
    }
}
