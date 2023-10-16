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
use Hyperf\Rpc;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Sentry\Tracing\SpanStatus;
use Throwable;

/**
 * @method array getConfig
 * @property array $config
 */
class HttpClientAspect extends AbstractAspect
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
            'coroutine.id' => Coroutine::id(),
            'http.method' => $method,
            'http.uri' => $uri,
            'config' => $guzzleConfig,
            'options' => $arguments['options'] ?? [],
        ];

        $context = SpanContext::create(
            'http.client',
            $method . ' ' . (string) $uri
        );

        $appendHeaders = [];
        if (
            $this->container->has(Rpc\Context::class)
            && $rpcContext = $this->container->get(Rpc\Context::class)
        ) {
            $appendHeaders = $rpcContext->get(TraceContext::RPC_CARRIER, []);
        }
        $options['headers'] = array_replace($options['headers'] ?? [], $appendHeaders);
        $proceedingJoinPoint->arguments['keys']['options']['headers'] = $options['headers'];

        try {
            $result = $proceedingJoinPoint->process();

            if ($result instanceof ResponseInterface) {
                $data = array_merge($data, [
                    'response.status' => $result->getStatusCode(),
                    'response.reason' => $result->getReasonPhrase(),
                    'response.headers' => $result->getHeaders(),
                ]);
            }

            $context->setStatus(SpanStatus::ok());
        } catch (Throwable $e) {
            $context->setStatus(SpanStatus::internalError());
            if (! $this->switcher->isExceptionIgnored($e)) {
                $data = array_merge($data, [
                    'exception.class' => get_class($e),
                    'exception.message' => $e->getMessage(),
                    'exception.code' => $e->getCode(),
                    'exception.stacktrace' => $e->getTraceAsString(),
                ]);
            }
            throw $e;
        } finally {
            $context->setData($data)->finish();
        }

        return $result;
    }
}
