<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Tracing\Aspect;

use FriendsOfHyperf\Sentry\Switcher;
use FriendsOfHyperf\Sentry\Tracing\SpanStarter;
use GuzzleHttp\Client;
use Hyperf\Context\Context;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanStatus;
use Throwable;

/**
 * @method array getConfig()
 * @property array $config
 */
class GuzzleHttpClientAspect extends AbstractAspect
{
    use SpanStarter;

    public array $classes = [
        Client::class . '::request',
        Client::class . '::requestAsync',
    ];

    public function __construct(
        protected ContainerInterface $container,
        protected Switcher $switcher
    ) {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (
            ! $this->switcher->isTracingSpanEnable('guzzle')
            || Context::get(RpcAspect::SPAN) // If the parent span is not exists or the parent span is belongs to rpc, then skip.
        ) {
            return $proceedingJoinPoint->process();
        }

        $instance = $proceedingJoinPoint->getInstance();
        $arguments = $proceedingJoinPoint->arguments['keys'];
        $options = $arguments['options'] ?? [];
        $guzzleConfig = (fn () => match (true) {
            method_exists($this, 'getConfig') => $this->getConfig(), // @deprecated ClientInterface::getConfig will be removed in guzzlehttp/guzzle:8.0.
            property_exists($this, 'config') => $this->config,
            default => [],
        })->call($instance);

        if (
            ($options['no_sentry_tracing'] ?? null) === true
            || ($guzzleConfig['no_sentry_tracing'] ?? null) === true
        ) {
            return $proceedingJoinPoint->process();
        }

        // Disable the aspect for the requestAsync method.
        if ($proceedingJoinPoint->methodName == 'request') {
            $proceedingJoinPoint->arguments['keys']['options']['no_sentry_tracing'] = true;
        }

        $uri = (string) ($arguments['uri'] ?? '/');
        $method = $arguments['method'] ?? 'GET';
        $fullUri = new \GuzzleHttp\Psr7\Uri($uri);

        $data = [
            // See: https://develop.sentry.dev/sdk/performance/span-data-conventions/#http
            'http.query' => $fullUri->getQuery(),
            'http.fragment' => $fullUri->getFragment(),
            'http.request.method' => $method,
            'http.request.body.size' => strlen($arguments['options']['body'] ?? ''),
            // Other
            'coroutine.id' => Coroutine::id(),
            'http.system' => 'guzzle',
            'http.guzzle.config' => $guzzleConfig,
            'http.guzzle.options' => $arguments['options'] ?? [],
        ];

        $parent = SentrySdk::getCurrentHub()->getSpan();
        $options['headers'] = array_replace($options['headers'] ?? [], [
            'sentry-trace' => $parent->toTraceparent(),
            'baggage' => $parent->toBaggage(),
            'traceparent' => $parent->toW3CTraceparent(),
        ]);
        $proceedingJoinPoint->arguments['keys']['options']['headers'] = $options['headers'];

        $span = $this->startSpan('http.client', $method . ' ' . (string) $uri);

        try {
            $result = $proceedingJoinPoint->process();

            if (! $span) {
                return $result;
            }

            if ($result instanceof ResponseInterface) {
                $data += [
                    'response.status' => $result->getStatusCode(),
                    'response.reason' => $result->getReasonPhrase(),
                    'response.headers' => $result->getHeaders(),
                ];
                if ($this->switcher->isTracingExtraTagEnable('response.body')) {
                    $data['response.body'] = $result->getBody()->getContents();
                    $result->getBody()->rewind();
                }
            }
        } catch (Throwable $exception) {
            $span->setStatus(SpanStatus::internalError());
            $span->setTags([
                'error' => true,
                'exception.class' => $exception::class,
                'exception.message' => $exception->getMessage(),
                'exception.code' => $exception->getCode(),
            ]);
            if ($this->switcher->isTracingExtraTagEnable('exception.stack_trace')) {
                $data['exception.stack_trace'] = (string) $exception;
            }

            throw $exception;
        } finally {
            $span->setData($data);
            $span->finish();
        }

        return $result;
    }
}
