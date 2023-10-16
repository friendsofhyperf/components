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

use Elasticsearch\Client;
use FriendsOfHyperf\Sentry\Switcher;
use FriendsOfHyperf\Sentry\Tracing\SpanContext;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\Tracing\SpanStatus;
use Throwable;

class ElasticserachAspect extends AbstractAspect
{
    public array $classes = [
        Client::class . '::bulk',
        Client::class . '::count',
        Client::class . '::create',
        Client::class . '::get',
        Client::class . '::getSource',
        Client::class . '::index',
        Client::class . '::mget',
        Client::class . '::msearch',
        Client::class . '::scroll',
        Client::class . '::search',
        Client::class . '::update',
        Client::class . '::updateByQuery',
        Client::class . '::search',
    ];

    public function __construct(protected Switcher $switcher)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->switcher->isTracingEnable('elasticserach')) {
            return $proceedingJoinPoint->process();
        }

        $context = SpanContext::create(
            'elasticserach.' . $proceedingJoinPoint->methodName,
            sprintf('%s::%s()', $proceedingJoinPoint->className, $proceedingJoinPoint->methodName),
        );

        $data = [
            'coroutine.id' => Coroutine::id(),
            'arguments' => $proceedingJoinPoint->arguments['keys'],
        ];

        try {
            $result = $proceedingJoinPoint->process();
            // $data['result'] = $result;
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
