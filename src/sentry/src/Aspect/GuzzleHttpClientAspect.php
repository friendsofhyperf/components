<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Sentry\Aspect;

use FriendsOfHyperf\Sentry\Integration;
use GuzzleHttp\Client;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\Breadcrumb;

class GuzzleHttpClientAspect extends AbstractAspect
{
    public array $classes = [
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

        return tap($proceedingJoinPoint->process(), function ($result) use ($instance, $arguments, $startTime) {
            if (! $this->config->get('sentry.breadcrumbs.guzzle', false)) {
                return;
            }

            $options = $arguments['keys']['options'] ?? [];

            if (($options['no_aspect'] ?? null) === true) {
                return;
            }

            $guzzleConfig = (function () {
                if (method_exists($this, 'getConfig')) { // @deprecated ClientInterface::getConfig will be removed in guzzlehttp/guzzle:8.0.
                    return $this->getConfig();
                }

                return $this->config ?? [];
            })->call($instance);

            if (($guzzleConfig['no_aspect'] ?? null) === true) {
                return;
            }

            $uri = $arguments['keys']['uri'] ?? '';
            $data['request']['uri'] = $guzzleConfig['base_uri'] . $uri;
            $data['request']['method'] = $arguments['keys']['method'] ?? 'GET';
            $data['request']['options'] = $arguments['keys']['options'] ?? [];
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
