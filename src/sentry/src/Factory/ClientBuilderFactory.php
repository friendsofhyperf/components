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

use FriendsOfHyperf\Sentry\Integration;
use FriendsOfHyperf\Sentry\Integration\RequestFetcher;
use FriendsOfHyperf\Sentry\Version;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Server\ServerManager;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Sentry\ClientBuilder;
use Sentry\Integration as SdkIntegration;

use function Hyperf\Support\env;
use function Hyperf\Support\make;
use function Hyperf\Tappable\tap;

class ClientBuilderFactory
{
    public const SPECIFIC_OPTIONS = [
        'breadcrumbs',
        'integrations',
        'dont_report',
    ];

    public function __invoke(ContainerInterface $container)
    {
        $usrConfig = $container->get(ConfigInterface::class)->get('sentry', []);

        foreach (static::SPECIFIC_OPTIONS as $specificOptionName) {
            unset($userConfig[$specificOptionName]);
        }

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

            $options = $clientBuilder->getOptions();
            $userIntegrations = $this->resolveIntegrationsFromUserConfig($container);

            $options->setIntegrations(static function (array $integrations) use ($options, $userIntegrations, $container) {
                if ($options->hasDefaultIntegrations()) {
                    // Remove the default error and fatal exception listeners to let Laravel handle those
                    // itself. These event are still bubbling up through the documented changes in the users
                    // `ExceptionHandler` of their application or through the log channel integration to Sentry
                    $integrations = array_filter($integrations, static function (SdkIntegration\IntegrationInterface $integration): bool {
                        if ($integration instanceof SdkIntegration\ErrorListenerIntegration) {
                            return false;
                        }

                        if ($integration instanceof SdkIntegration\ExceptionListenerIntegration) {
                            return false;
                        }

                        if ($integration instanceof SdkIntegration\FatalErrorListenerIntegration) {
                            return false;
                        }

                        // We also remove the default request integration so it can be readded
                        // after with a Laravel specific request fetcher. This way we can resolve
                        // the request from Laravel instead of constructing it from the global state
                        if ($integration instanceof SdkIntegration\RequestIntegration) {
                            return false;
                        }

                        return true;
                    });
                }

                $requestFetcher = null;

                if (class_exists(ServerManager::class) && ServerManager::list()) {
                    $requestFetcher = $container->get(RequestFetcher::class);
                }

                $integrations[] = new SdkIntegration\RequestIntegration($requestFetcher);

                return array_merge($integrations, $userIntegrations);
            });
        });
    }

    protected function resolveIntegrationsFromUserConfig(ContainerInterface $container): array
    {
        $integrations = [
            new Integration(),
        ];
        $config = $container->get(ConfigInterface::class)->get('sentry', []);
        $userIntegrations = $config['integrations'] ?? [];

        foreach ($userIntegrations as $userIntegration) {
            if ($userIntegration instanceof SdkIntegration\IntegrationInterface) {
                $integrations[] = $userIntegration;
            } elseif (\is_string($userIntegration)) {
                $resolvedIntegration = make($userIntegration);

                if (! $resolvedIntegration instanceof SdkIntegration\IntegrationInterface) {
                    throw new RuntimeException('Sentry integrations should a instance of `\Sentry\Integration\IntegrationInterface`.');
                }

                $integrations[] = $resolvedIntegration;
            } else {
                throw new RuntimeException('Sentry integrations should either be a container reference or a instance of `\Sentry\Integration\IntegrationInterface`.');
            }
        }

        return $integrations;
    }
}
