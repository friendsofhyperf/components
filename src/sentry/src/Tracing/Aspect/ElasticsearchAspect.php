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
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\Tracing\SpanStatus;
use Throwable;

class ElasticsearchAspect extends AbstractAspect
{
    use SpanStarter;

    public array $classes = [
        // v7.x
        'Elasticsearch\Client::bulk',
        'Elasticsearch\Client::count',
        'Elasticsearch\Client::create',
        'Elasticsearch\Client::get',
        'Elasticsearch\Client::getSource',
        'Elasticsearch\Client::index',
        'Elasticsearch\Client::mget',
        'Elasticsearch\Client::msearch',
        'Elasticsearch\Client::scroll',
        'Elasticsearch\Client::search',
        'Elasticsearch\Client::update',
        'Elasticsearch\Client::updateByQuery',
        // v8.x
        'Elastic\Elasticsearch\Client::bulk',
        'Elastic\Elasticsearch\Client::count',
        'Elastic\Elasticsearch\Client::create',
        'Elastic\Elasticsearch\Client::get',
        'Elastic\Elasticsearch\Client::getSource',
        'Elastic\Elasticsearch\Client::index',
        'Elastic\Elasticsearch\Client::mget',
        'Elastic\Elasticsearch\Client::msearch',
        'Elastic\Elasticsearch\Client::scroll',
        'Elastic\Elasticsearch\Client::search',
        'Elastic\Elasticsearch\Client::update',
        'Elastic\Elasticsearch\Client::updateByQuery',
    ];

    public function __construct(
        protected Switcher $switcher,
        protected TagManager $tagManager
    ) {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->switcher->isTracingSpanEnable('elasticsearch')) {
            return $proceedingJoinPoint->process();
        }

        $span = $this->startSpan(
            'elasticserach.' . $proceedingJoinPoint->methodName,
            sprintf('%s::%s()', $proceedingJoinPoint->className, $proceedingJoinPoint->methodName),
        );

        if (! $span) {
            return $proceedingJoinPoint->process();
        }

        $data = [];

        if ($this->tagManager->has('elasticserach.coroutine.id')) {
            $data[$this->tagManager->get('elasticserach.coroutine.id')] = Coroutine::id();
        }

        if ($this->tagManager->has('elasticserach.arguments')) {
            $data[$this->tagManager->get('elasticserach.arguments')] = json_encode($proceedingJoinPoint->arguments['keys'], JSON_UNESCAPED_UNICODE);
        }

        try {
            $result = $proceedingJoinPoint->process();
            if ($this->tagManager->has('elasticserach.result')) {
                $data[$this->tagManager->get('elasticserach.result')] = json_encode($result, JSON_UNESCAPED_UNICODE);
            }
        } catch (Throwable $exception) {
            $span->setStatus(SpanStatus::internalError());
            $span->setTags([
                'error' => true,
                'exception.class' => $exception::class,
                'exception.message' => $exception->getMessage(),
                'exception.code' => $exception->getCode(),
            ]);
            if ($this->tagManager->has('elasticserach.exception.stack_trace')) {
                $data[$this->tagManager->get('elasticserach.exception.stack_trace')] = (string) $exception;
            }

            throw $exception;
        } finally {
            $span->setData($data);
            $span->finish();
        }

        return $result;
    }
}
