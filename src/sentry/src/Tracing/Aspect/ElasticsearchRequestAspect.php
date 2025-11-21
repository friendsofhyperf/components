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

use FriendsOfHyperf\Sentry\Constants;
use Hyperf\Context\Context;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

/**
 * @method string getFullUrl()
 */
class ElasticsearchRequestAspect extends AbstractAspect
{
    public array $classes = [
        'Elasticsearch\Client::performRequest',
        'Elastic\Elasticsearch\Client::sendRequest',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $client = $proceedingJoinPoint->getInstance();

        if ($proceedingJoinPoint->methodName === 'performRequest') { // ES 7.x
            $lastConnection = $client->transport->getLastConnection();
            $data = [
                'server.address' => $lastConnection->getHost(),
                'server.port' => $lastConnection->getPort(),
                'http.request.method' => $lastConnection->getLastRequestInfo()['request']['http_method'] ?? 'GET',
                'url.full' => $lastConnection->getLastRequestInfo()['response']['effective_url'] ?? '',
            ];
            Context::set(Constants::TRACE_ELASTICSEARCH_REQUEST_DATA, $data);
        }

        if ($proceedingJoinPoint->methodName === 'sendRequest') { // ES 8.x
            $transport = $client->getTransport();
            $lastRequest = $transport->getLastRequest();
            $data = [
                'server.address' => $lastRequest->getUri()->getHost(),
                'server.port' => $lastRequest->getUri()->getPort(),
                'http.request.method' => $lastRequest->getMethod(),
                'url.full' => (fn ($request) => $this->getFullUrl($request))->call($transport, $lastRequest),
            ];
            Context::set(Constants::TRACE_ELASTICSEARCH_REQUEST_DATA, $data);
        }

        return $proceedingJoinPoint->process();
    }
}
