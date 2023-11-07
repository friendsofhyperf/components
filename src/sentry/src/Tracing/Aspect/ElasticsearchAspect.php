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

class ElasticsearchAspect extends AbstractAspect
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
        if (! $this->switcher->isTracingEnable('elasticsearch')) {
            return $proceedingJoinPoint->process();
        }

        $context = SpanContext::create(
            'elasticserach.' . $proceedingJoinPoint->methodName,
            sprintf('%s::%s()', $proceedingJoinPoint->className, $proceedingJoinPoint->methodName),
        );
        $data = [
            'elasticserach.coroutine.id' => Coroutine::id(),
            'elasticserach.method' => $proceedingJoinPoint->methodName,
            'elasticserach.arguments' => json_encode($proceedingJoinPoint->arguments['keys'], JSON_UNESCAPED_UNICODE),
        ];
        $tags = [];
        $status = SpanStatus::ok();

        try {
            $result = $proceedingJoinPoint->process();
            $data['elasticserach.result'] = json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (Throwable $exception) {
            $status = SpanStatus::internalError();
            $tags = array_merge($tags, [
                'error' => true,
                'exception.class' => $exception::class,
                'exception.message' => $exception->getMessage(),
                'exception.code' => $exception->getCode(),
            ]);
            $data['elasticserach.exception.stack_trace'] = (string) $exception;

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
