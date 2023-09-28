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
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\HttpServer\Event;
use Hyperf\HttpServer\Event\RequestTerminated;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sentry\SentrySdk;
use Sentry\State\HubInterface;
use Sentry\Tracing\Span;
use Sentry\Tracing\SpanContext;
use Sentry\Tracing\Transaction;
use Sentry\Tracing\TransactionSource;

use function Sentry\continueTrace;

class TracingListener implements ListenerInterface
{
    /**
     * The timestamp of application bootstrap completion.
     */
    private ?float $bootedTimestamp = null;

    public function __construct(protected ContainerInterface $container, protected Switcher $switcher)
    {
        $this->setBootedTimestamp();
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
        if (! $this->switcher->isEnable('tracing')) {
            return;
        }

        SentrySdk::getCurrentHub();

        match ($event::class) {
            Event\RequestTerminated::class => $this->finishTransaction($event),
            Event\RequestHandled::class => $this->appendTraceId($event),
            default => $this->startTransaction($event->request, SentrySdk::getCurrentHub(), $event->server),
        };
    }

    /**
     * Set the timestamp of application bootstrap completion.
     *
     * @param float|null $timestamp the unix timestamp of the booted event, default to `microtime(true)` if not `null`
     *
     * @internal this method should only be invoked right after the application has finished "booting"
     */
    public function setBootedTimestamp(?float $timestamp = null): void
    {
        $this->bootedTimestamp = $timestamp ?? microtime(true);
    }

    private function startTransaction(ServerRequestInterface $request, HubInterface $sentry, string $server = 'http'): void
    {
        // Try $_SERVER['REQUEST_TIME_FLOAT'] then LARAVEL_START and fallback to microtime(true) if neither are defined
        $requestStartTime = microtime(true);

        $context = continueTrace(
            $request->getHeaderLine('sentry-trace', ''),
            $request->getHeaderLine('baggage', '')
        );

        $requestPath = $request->getUri()->getPath();

        $context->setOp($server . '.server');
        $context->setDescription(sprintf(
            'request: %s %s',
            $request->getMethod(),
            $requestPath
        ));
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

        SentrySdk::getCurrentHub()->setSpan($transaction);

        $bootstrapSpan = $this->addAppBootstrapSpan();

        $appContextStart = new SpanContext();
        $appContextStart->setOp('request.received');
        $appContextStart->setStartTimestamp($bootstrapSpan ? $bootstrapSpan->getEndTimestamp() : microtime(true));

        $appSpan = $transaction->startChild($appContextStart);
        TraceContext::setRoot($appSpan);

        SentrySdk::getCurrentHub()->setSpan($appSpan);
    }

    private function addAppBootstrapSpan(): ?Span
    {
        if ($this->bootedTimestamp === null || ! $transaction = TraceContext::getTransaction()) {
            return null;
        }

        $spanContextStart = new SpanContext();
        $spanContextStart->setOp('app.bootstrap');
        $spanContextStart->setStartTimestamp($transaction->getStartTimestamp());
        $spanContextStart->setEndTimestamp($this->bootedTimestamp);

        $span = $transaction->startChild($spanContextStart);

        // Consume the booted timestamp, because we don't want to report the bootstrap span more than once
        $this->bootedTimestamp = null;

        // Add more information about the bootstrap section if possible
        $this->addBootDetailTimeSpans($span);

        return $span;
    }

    private function addBootDetailTimeSpans(Span $bootstrap): void
    {
        // This constant should be defined right after the composer `autoload.php` require statement in `public/index.php`
        // define('SENTRY_AUTOLOAD', microtime(true));
        if (! defined('SENTRY_AUTOLOAD') || ! SENTRY_AUTOLOAD || $transaction = TraceContext::getTransaction()) {
            return;
        }

        $autoload = new SpanContext();
        $autoload->setOp('app.php.autoload');
        $autoload->setStartTimestamp($transaction->getStartTimestamp());
        $autoload->setEndTimestamp(SENTRY_AUTOLOAD);

        $bootstrap->startChild($autoload);
    }

    /**
     * @param RequestTerminated $event
     */
    private function finishTransaction(object $event): void
    {
        $transaction = TraceContext::getTransaction();

        if ($transaction === null) {
            return;
        }

        if ($appSpan = TraceContext::getRoot()) {
            $appSpan->finish();
            TraceContext::clearRoot();
        }

        $transaction->setHttpStatus($event->response->getStatusCode());

        if ($exception = $event->exception) {
            if (! $this->switcher->isExceptionIgnored($exception)) {
                $transaction->setTags([
                    'error' => true,
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
    }

    /**
     * @param Event\RequestHandled $event
     */
    private function appendTraceId(object $event): void
    {
        $response = $event->response;

        if (method_exists($response, 'addHeader')) {
            $response->addHeader(
                'sentry-trace',
                (string) TraceContext::getTransaction()?->getTraceId()
            );
        }
    }
}
