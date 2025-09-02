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
        'Elastic\Elasticsearch\Traits\ClientEndpointsTrait::bulk',
        'Elastic\Elasticsearch\Traits\ClientEndpointsTrait::count',
        'Elastic\Elasticsearch\Traits\ClientEndpointsTrait::create',
        'Elastic\Elasticsearch\Traits\ClientEndpointsTrait::get',
        'Elastic\Elasticsearch\Traits\ClientEndpointsTrait::getSource',
        'Elastic\Elasticsearch\Traits\ClientEndpointsTrait::index',
        'Elastic\Elasticsearch\Traits\ClientEndpointsTrait::mget',
        'Elastic\Elasticsearch\Traits\ClientEndpointsTrait::msearch',
        'Elastic\Elasticsearch\Traits\ClientEndpointsTrait::scroll',
        'Elastic\Elasticsearch\Traits\ClientEndpointsTrait::search',
        'Elastic\Elasticsearch\Traits\ClientEndpointsTrait::update',
        'Elastic\Elasticsearch\Traits\ClientEndpointsTrait::updateByQuery',
    ];

    public function __construct(protected Switcher $switcher)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->switcher->isTracingSpanEnable('elasticsearch')) {
            return $proceedingJoinPoint->process();
        }

        // TODO 规则: opeate dbName.tableName
        $span = $this->startSpan(
            'db.elasticserach',
            sprintf('%s::%s()', $proceedingJoinPoint->className, $proceedingJoinPoint->methodName),
        );

        if (! $span) {
            return $proceedingJoinPoint->process();
        }

        $data = [
            'coroutine.id' => Coroutine::id(),
            'db.system' => 'elasticsearch',
            'db.operation.name' => $proceedingJoinPoint->methodName,
            'http.request.method' => '', // TODO
            'url.full' => '', // TODO
            'server.host' => '', // TODO
            'server.port' => '', // TODO
            'arguments' => json_encode($proceedingJoinPoint->arguments['keys'], JSON_UNESCAPED_UNICODE),
        ];

        try {
            $result = $proceedingJoinPoint->process();
            if ($this->switcher->isTracingExtraTagEnable('elasticsearch.result')) {
                $data['elasticsearch.result'] = json_encode($result, JSON_UNESCAPED_UNICODE);
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
            $span->setOrigin('auto.elasticsearch')->setData($data)->finish();
        }

        return $result;
    }
}
