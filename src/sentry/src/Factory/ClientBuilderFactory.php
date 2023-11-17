<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Factory;

use FriendsOfHyperf\Sentry\Version;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Sentry\ClientBuilder;

use function Hyperf\Support\env;
use function Hyperf\Tappable\tap;

class ClientBuilderFactory
{
    public const SPECIFIC_OPTIONS = [
        'breadcrumbs',
        'ignore_commands',
        'integrations',
        'enable',
        'tracing',
    ];

    public function __invoke(ContainerInterface $container)
    {
        $userConfig = $container->get(ConfigInterface::class)->get('sentry', []);
        $userConfig['enable_tracing'] ??= true;

        foreach (static::SPECIFIC_OPTIONS as $specificOptionName) {
            if (isset($userConfig[$specificOptionName])) {
                unset($userConfig[$specificOptionName]);
            }
        }

        if (isset($userConfig['logger'])) {
            if (is_string($userConfig['logger']) && $container->has($userConfig['logger'])) {
                $userConfig['logger'] = $container->get($userConfig['logger']);
            }
            if (! $userConfig['logger'] instanceof \Psr\Log\LoggerInterface) {
                unset($userConfig['logger']);
            }
        }

        $options = array_merge(
            [
                'prefixes' => [BASE_PATH],
                'in_app_exclude' => [BASE_PATH . '/vendor'],
            ],
            $userConfig
        );

        // When we get no environment from the (user) configuration we default to the environment
        if (empty($options['environment'])) {
            $options['environment'] = env('APP_ENV', 'production');
        }

        return tap(ClientBuilder::create($options), function (ClientBuilder $clientBuilder) {
            $clientBuilder->setSdkIdentifier(Version::SDK_IDENTIFIER);
            $clientBuilder->setSdkVersion(Version::SDK_VERSION);
        });
    }
}
