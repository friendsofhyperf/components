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

use FriendsOfHyperf\Sentry\Feature;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\State\Scope;
use Sentry\Tracing\SpanContext;

use function FriendsOfHyperf\Sentry\trace;

class ElasticsearchAspect extends AbstractAspect
{
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

    public function __construct(protected Feature $feature)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->feature->isTracingSpanEnabled('elasticsearch')) {
            return $proceedingJoinPoint->process();
        }

        return trace(
            function (Scope $scope) use ($proceedingJoinPoint) {
                $result = $proceedingJoinPoint->process();
                if ($this->feature->isTracingExtraTagEnabled('elasticsearch.result')) {
                    $scope->getSpan()?->setData([
                        'elasticsearch.result' => (string) json_encode($result, JSON_UNESCAPED_UNICODE),
                    ]);
                }
                return $result;
            },
            SpanContext::make()
                ->setOp('db.query')
                ->setDescription(sprintf('%s::%s()', $proceedingJoinPoint->className, $proceedingJoinPoint->methodName))
                ->setOrigin('auto.db.elasticsearch')
                ->setData([
                    'db.system' => 'elasticsearch',
                    'db.operation.name' => $proceedingJoinPoint->methodName,
                    'arguments' => (string) json_encode($proceedingJoinPoint->arguments['keys'], JSON_UNESCAPED_UNICODE),
                    // TODO
                    // 'http.request.method' => '',
                    // 'url.full' => '',
                    // 'server.host' => '',
                    // 'server.port' => '',
                ])
        );
    }
}
