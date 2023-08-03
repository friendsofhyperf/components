<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Sentry\Aspect;

use FriendsOfHyperf\Sentry\Integration;
use GuzzleHttp\Client;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Psr\Http\Message\ResponseInterface;
use Sentry\Breadcrumb;

use function Hyperf\Tappable\tap;

class GuzzleHttpClientAspect extends AbstractAspect
{
    public array $classes = [
        Client::class . '::request',
        Client::class . '::requestAsync',
    ];

    public function __construct(protected ConfigInterface $config)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $startTime = microtime(true);
        $instance = $proceedingJoinPoint->getInstance();
        $arguments = $proceedingJoinPoint->arguments;

        if ($proceedingJoinPoint->methodName == 'request') { // Disable the aspect for the requestAsync method.
            $proceedingJoinPoint->arguments['keys']['options']['no_sentry_aspect'] = true;
        }

        return tap($proceedingJoinPoint->process(), function ($result) use ($instance, $arguments, $startTime) {
            if (! $this->config->get('sentry.breadcrumbs.guzzle', false)) {
                return;
            }

            $options = $arguments['keys']['options'] ?? [];

            if (($options['no_sentry_aspect'] ?? null) === true) {
                return;
            }

            $guzzleConfig = (function () {
                if (method_exists($this, 'getConfig')) { // @deprecated ClientInterface::getConfig will be removed in guzzlehttp/guzzle:8.0.
                    return $this->getConfig();
                }

                return $this->config ?? [];
            })->call($instance);

            if (($guzzleConfig['no_sentry_aspect'] ?? null) === true) {
                return;
            }

            $uri = $arguments['keys']['uri'] ?? '';
            $data['config'] = $guzzleConfig;
            $data['request']['method'] = $arguments['keys']['method'] ?? 'GET';
            $data['request']['options'] = $arguments['keys']['options'] ?? [];
            if ($result instanceof ResponseInterface) {
                $data['response']['status'] = $result->getStatusCode();
                $data['response']['reason'] = $result->getReasonPhrase();
                $data['response']['headers'] = $result->getHeaders();
            }
            $data['timeMs'] = (microtime(true) - $startTime) * 1000;

            Integration::addBreadcrumb(new Breadcrumb(
                Breadcrumb::LEVEL_INFO,
                Breadcrumb::TYPE_DEFAULT,
                'guzzle',
                $uri,
                $data
            ));
        });
    }
}
