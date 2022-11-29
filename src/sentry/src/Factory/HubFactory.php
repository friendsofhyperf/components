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

use FriendsOfHyperf\Sentry\Integration;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Sentry\ClientBuilderInterface;
use Sentry\Integration as SdkIntegration;
use Sentry\SentrySdk;
use Sentry\State\Hub;

class HubFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $clientBuilder = $container->get(ClientBuilderInterface::class);

        $options = $clientBuilder->getOptions();

        $userIntegrations = $this->resolveIntegrationsFromUserConfig($container);

        $options->setIntegrations(static function (array $integrations) use ($options, $userIntegrations) {
            $allIntegrations = array_merge($integrations, $userIntegrations);

            if (! $options->hasDefaultIntegrations()) {
                return $allIntegrations;
            }

            // Remove the default error and fatal exception listeners to let Laravel handle those
            // itself. These event are still bubbling up through the documented changes in the users
            // `ExceptionHandler` of their application or through the log channel integration to Sentry
            return array_filter($allIntegrations, static function (SdkIntegration\IntegrationInterface $integration): bool {
                if ($integration instanceof SdkIntegration\ErrorListenerIntegration) {
                    return false;
                }

                if ($integration instanceof SdkIntegration\ExceptionListenerIntegration) {
                    return false;
                }

                if ($integration instanceof SdkIntegration\FatalErrorListenerIntegration) {
                    return false;
                }

                return true;
            });
        });

        return tap(new Hub($clientBuilder->getClient()), fn ($hub) => SentrySdk::setCurrentHub($hub));
    }

    protected function resolveIntegrationsFromUserConfig(ContainerInterface $container): array
    {
        $integrations = [new Integration()];

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
