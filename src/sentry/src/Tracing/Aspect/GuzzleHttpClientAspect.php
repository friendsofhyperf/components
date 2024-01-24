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
use FriendsOfHyperf\Sentry\Tracing\TagManager;
use GuzzleHttp\Client;
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
        protected Switcher $switcher,
        protected TagManager $tagManager
    ) {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->switcher->isTracingSpanEnable('guzzle')) {
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
        $data = [];

        if ($this->tagManager->has('guzzle.coroutine.id')) {
            $data[$this->tagManager->get('guzzle.coroutine.id')] = Coroutine::id();
        }
        if ($this->tagManager->has('guzzle.http.method')) {
            $data[$this->tagManager->get('guzzle.http.method')] = $method;
        }
        if ($this->tagManager->has('guzzle.http.uri')) {
            $data[$this->tagManager->get('guzzle.http.uri')] = (string) $uri;
        }
        if ($this->tagManager->has('guzzle.guzzle.config')) {
            $data[$this->tagManager->get('guzzle.guzzle.config')] = $guzzleConfig;
        }
        if ($this->tagManager->has('guzzle.request.options')) {
            $data[$this->tagManager->get('guzzle.request.options')] = $arguments['options'] ?? [];
        }

        $parent = SentrySdk::getCurrentHub()->getSpan();
        $options['headers'] = array_replace($options['headers'] ?? [], [
            'sentry-trace' => $parent->toTraceparent(),
            'baggage' => $parent->toBaggage(),
            'traceparent' => $parent->toW3CTraceparent(),
        ]);
        $proceedingJoinPoint->arguments['keys']['options']['headers'] = $options['headers'];

        $span = $this->startSpan(
            'http.client',
            $method . ' ' . (string) $uri
        );

        try {
            $result = $proceedingJoinPoint->process();
            if (! $span) {
                return $result;
            }

            if ($result instanceof ResponseInterface) {
                if ($this->tagManager->has('guzzle.response.status')) {
                    $data[$this->tagManager->get('guzzle.response.status')] = $result->getStatusCode();
                }
                if ($this->tagManager->has('guzzle.response.reason')) {
                    $data[$this->tagManager->get('guzzle.response.reason')] = $result->getReasonPhrase();
                }
                if ($this->tagManager->has('guzzle.response.headers')) {
                    $data[$this->tagManager->get('guzzle.response.headers')] = $result->getHeaders();
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
            if ($this->tagManager->has('guzzle.exception.stack_trace')) {
                $data[$this->tagManager->get('guzzle.exception.stack_trace')] = (string) $exception;
            }

            throw $exception;
        } finally {
            $span->setData($data);
            $span->finish();
        }

        return $result;
    }
}
