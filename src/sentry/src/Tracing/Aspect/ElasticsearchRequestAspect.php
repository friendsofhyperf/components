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
        'Elastic\Elasticsearch\Client::sendRequest',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $arguments = $proceedingJoinPoint->arguments['keys'] ?? [];
        $request = $arguments['request'] ?? null;

        if ($request instanceof RequestInterface) {
            $data = [
                'server.address' => $request->getUri()->getHost(),
                'server.port' => $request->getUri()->getPort(),
                'http.request.method' => $request->getMethod(),
                'url.full' => $this->getFullUrl($request),
            ];
            Context::set(Constants::TRACE_ELASTICSEARCH_REQUEST_DATA, $data);
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
