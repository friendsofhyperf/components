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
use Psr\Http\Message\RequestInterface;

class ElasticsearchRequestAspect extends AbstractAspect
{
    public array $classes = [
        'Elasticsearch\Client::performRequest',
        'Elastic\Elasticsearch\Client::sendRequest',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if ($proceedingJoinPoint->methodName === 'performRequest') { // ES 7.x
            $client = $proceedingJoinPoint->getInstance();
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
            $request = $proceedingJoinPoint->arguments['keys']['request'] ?? null;
            if ($request instanceof RequestInterface) {
                $data = [
                    'server.address' => $request->getUri()->getHost(),
                    'server.port' => $request->getUri()->getPort(),
                    'http.request.method' => $request->getMethod(),
                    'url.full' => $this->getFullUrl($request),
                ];
                Context::set(Constants::TRACE_ELASTICSEARCH_REQUEST_DATA, $data);
            }
        }

        return $proceedingJoinPoint->process();
    }

    /**
     * Return the full URL in the format
     * scheme://host:port/path?query_string.
     */
    private function getFullUrl(RequestInterface $request): string
    {
        $fullUrl = sprintf(
            '%s://%s:%s%s',
            $request->getUri()->getScheme(),
            $request->getUri()->getHost(),
            $request->getUri()->getPort(),
            $request->getUri()->getPath()
        );
        $queryString = $request->getUri()->getQuery();
        if (! empty($queryString)) {
            $fullUrl .= '?' . $queryString;
        }
        return $fullUrl;
    }
}
