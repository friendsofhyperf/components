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
use function Hyperf\Tappable\tap;

class ElasticsearchAspect extends AbstractAspect
{
    public array $classes = [
        // v8.x+
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
                return tap($proceedingJoinPoint->process(), function ($result) use ($scope, $proceedingJoinPoint) {
                    if ($this->feature->isTracingTagEnabled('elasticsearch.result')) {
                        $scope->getSpan()?->setData([
                            'elasticsearch.result' => (string) json_encode($result, JSON_UNESCAPED_UNICODE),
                        ]);
                    }

                    /** @var \Elastic\Elasticsearch\Client */
                    $client = $proceedingJoinPoint->getInstance();
                    $transport = $client->getTransport();
                    $lastRequest = $transport->getLastRequest();
                    $data = [
                        'server.address' => $lastRequest->getUri()->getHost(),
                        'server.port' => $lastRequest->getUri()->getPort(),
                        'http.request.method' => $lastRequest->getMethod(),
                        'url.full' => (fn ($request) => $this->getFullUrl($request))->call($transport, $lastRequest),
                    ];
                    $scope->getSpan()?->setData($data);
                });
            },
            SpanContext::make()
                ->setOp('db.query')
                ->setDescription(sprintf('%s::%s()', $proceedingJoinPoint->className, $proceedingJoinPoint->methodName))
                ->setOrigin('auto.db.elasticsearch')
                ->setData([
                    'db.system' => 'elasticsearch',
                    'db.operation.name' => $proceedingJoinPoint->methodName,
                    'arguments' => (string) json_encode($proceedingJoinPoint->arguments['keys'], JSON_UNESCAPED_UNICODE),
                ])
        );
    }
}
