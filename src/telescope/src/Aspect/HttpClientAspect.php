<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope\Aspect;

use FriendsOfHyperf\Telescope\IncomingEntry;
use FriendsOfHyperf\Telescope\SwitchManager;
use FriendsOfHyperf\Telescope\Telescope;
use GuzzleHttp\Client;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Psr\Http\Message\ResponseInterface;

/**
 * @property array $config
 */
class HttpClientAspect extends AbstractAspect
{
    public array $classes = [
        Client::class . '::request',
        Client::class . '::requestAsync',
    ];

    public function __construct(protected SwitchManager $switcherManager)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->switcherManager->isEnable('guzzle')) {
            return $proceedingJoinPoint->process();
        }
        $startTime = microtime(true);
        $instance = $proceedingJoinPoint->getInstance();
        $arguments = $proceedingJoinPoint->arguments;
        $options = $arguments['keys']['options'] ?? [];
        $guzzleConfig = (fn () => $this->config ?? [])->call($instance);

        if (($options['no_telescope_aspect'] ?? null) === true || ($guzzleConfig['no_telescope_aspect'] ?? null) === true) {
            return $proceedingJoinPoint->process();
        }

        // Disable the aspect for the requestAsync method.
        if ($proceedingJoinPoint->methodName == 'request') {
            $proceedingJoinPoint->arguments['keys']['options']['no_telescope_aspect'] = true;
        }

        $arguments = $proceedingJoinPoint->arguments;
        $method = $arguments['keys']['method'] ?? 'GET';
        $uri = $arguments['keys']['uri'] ?? '';
        $headers = $options['headers'] ?? [];

        $result = $proceedingJoinPoint->process();

        $response = [];
        if ($result instanceof ResponseInterface) {
            $response['status'] = $result->getStatusCode();
            $response['reason'] = $result->getReasonPhrase();
            $response['headers'] = $result->getHeaders();
        }
        Telescope::recordClientRequest(IncomingEntry::make([
            'method' => $method,
            'uri' => $uri,
            'headers' => $headers,
            'response_status' => $response['status'] ?? 0,
            'response_headers' => $response['headers'] ?? '',
            'response' => $response,
            'duration' => floor((microtime(true) - $startTime) * 1000),
        ]));
        return $result;
    }
}
