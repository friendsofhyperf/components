<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Sentry\Factory;

use FriendsOfHyperf\Sentry\Version;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Sentry\ClientBuilder;

class ClientBuilderFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $usrConfig = $container->get(ConfigInterface::class)->get('sentry', []);

        unset(
            $usrConfig['breadcrumbs'],
            $usrConfig['integrations'],
        );

        $options = array_merge(
            [
                'prefixes' => [BASE_PATH],
                'in_app_exclude' => [BASE_PATH . '/vendor'],
            ],
            $usrConfig
        );

        // When we get no environment from the (user) configuration we default to the Laravel environment
        if (empty($options['environment'])) {
            $options['environment'] = env('APP_ENV', 'production');
        }

        return tap(ClientBuilder::create($options), function (ClientBuilder $clientBuilder) use ($container) {
            $clientBuilder->setSdkIdentifier(Version::SDK_IDENTIFIER);
            $clientBuilder->setSdkVersion(Version::SDK_VERSION);
            $clientBuilder->setLogger($container->get(LoggerFactory::class)->get('sentry'));
        });
    }
}
