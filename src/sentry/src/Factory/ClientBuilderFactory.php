<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/sentry.
 *
 * @link     https://github.com/friendsofhyperf/sentry
 * @document https://github.com/friendsofhyperf/sentry/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Sentry\Factory;

use FriendsOfHyperf\Sentry\Version;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Server\ServerManager;
use Psr\Container\ContainerInterface;
use Sentry\ClientBuilder;
use Sentry\Integration\RequestFetcher;
use Sentry\Integration\RequestIntegration;

class ClientBuilderFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class)->get('sentry', []);

        unset(
            $config['breadcrumbs'],
            $config['integrations'],
        );

        $fetcher = null;

        if (class_exists('Hyperf\Server\ServerManager') && ServerManager::list()) {
            $fetcher = $container->get(RequestFetcher::class);
        }

        $options = array_merge(
            [
                'prefixes' => [BASE_PATH],
                'in_app_exclude' => [BASE_PATH . '/vendor'],
                'integrations' => [
                    new RequestIntegration($fetcher),
                ],
            ],
            $config
        );

        return tap(ClientBuilder::create($options), function (ClientBuilder $clientBuilder) use ($container) {
            $clientBuilder->setSdkIdentifier(Version::SDK_IDENTIFIER);
            $clientBuilder->setSdkVersion(Version::SDK_VERSION);
            $clientBuilder->setLogger($container->get(LoggerFactory::class)->get('sentry'));
        });
    }
}
