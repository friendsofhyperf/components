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
use Hyperf\Di\Aop\AroundInterface;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\Breadcrumb;

class HttpClientAspect implements AroundInterface
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
        $arguments = $proceedingJoinPoint->arguments;
        $options = $proceedingJoinPoint->arguments['keys']['options'];

        return tap($proceedingJoinPoint->process(), function ($result) use ($arguments, $options, $startTime) {
            if (! $this->config->get('sentry.breadcrumbs.guzzle', false)) {
                return;
            }

            if (isset($options['no_aspect']) && $options['no_aspect'] === true) {
                return;
            }

            $uri = $arguments['keys']['uri'] ?? '';
            $data['request']['method'] = $options['method'] ?? 'GET';
            $data['request']['headers'] = $options['headers'] ?? [];
            $data['request']['query'] = $options['query'] ?? [];
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
