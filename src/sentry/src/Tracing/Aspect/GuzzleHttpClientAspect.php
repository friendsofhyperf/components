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
use FriendsOfHyperf\Sentry\Tracing\SpanContext;
use FriendsOfHyperf\Sentry\Tracing\TraceContext;
use GuzzleHttp\Client;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Sentry\Tracing\SpanStatus;
use Throwable;

/**
 * @method array getConfig
 * @property array $config
 */
class GuzzleHttpClientAspect extends AbstractAspect
{
    public array $classes = [
        Client::class . '::request',
        Client::class . '::requestAsync',
    ];

    public function __construct(protected ContainerInterface $container, protected Switcher $switcher)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->switcher->isTracingEnable('guzzle')) {
            return $proceedingJoinPoint->process();
        }

        $instance = $proceedingJoinPoint->getInstance();
        $arguments = $proceedingJoinPoint->arguments['keys'];
        $options = $arguments['options'] ?? [];
        $guzzleConfig = (function () {
            if (method_exists($this, 'getConfig')) { // @deprecated ClientInterface::getConfig will be removed in guzzlehttp/guzzle:8.0.
                return $this->getConfig();
            }

            return $this->config ?? [];
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

        $uri = $arguments['uri'] ?? '/';
        $method = $arguments['method'] ?? 'GET';
        $data = [
            'guzzle.coroutine.id' => Coroutine::id(),
            'guzzle.http.method' => $method,
            'guzzle.http.uri' => (string) $uri,
            'guzzle.guzzle.config' => $guzzleConfig,
            'guzzle.request.options' => $arguments['options'] ?? [],
        ];
        $tags = [];

        $context = SpanContext::create(
            'http.client',
            $method . ' ' . (string) $uri
        );
        $status = SpanStatus::ok();

        $parent = TraceContext::getSpan();
        $options['headers'] = array_replace($options['headers'] ?? [], [
            'sentry-trace' => $parent->toTraceparent(),
            'baggage' => $parent->toBaggage(),
        ]);
        $proceedingJoinPoint->arguments['keys']['options']['headers'] = $options['headers'];

        try {
            $result = $proceedingJoinPoint->process();

            if ($result instanceof ResponseInterface) {
                $data['guzzle.response.status'] = $result->getStatusCode();
                $data['guzzle.response.reason'] = $result->getReasonPhrase();
                $data['guzzle.response.headers'] = $result->getHeaders();
            }
        } catch (Throwable $exception) {
            $status = SpanStatus::internalError();
            $tags = array_merge($tags, [
                'error' => true,
                'exception.class' => $exception::class,
                'exception.message' => $exception->getMessage(),
                'exception.code' => $exception->getCode(),
            ]);
            $data['guzzle.exception.stack_trace'] = (string) $exception;

            throw $exception;
        } finally {
            $context->setStatus($status)
                ->setTags($tags)
                ->setData($data)
                ->finish();
        }

        return $result;
    }
}
