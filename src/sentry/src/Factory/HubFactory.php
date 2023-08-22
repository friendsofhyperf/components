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
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Sentry\ClientBuilderInterface;
use Sentry\Integration as SdkIntegration;
use Sentry\State\Hub;

use function Hyperf\Support\make;

/**
 * @property \Sentry\Transport\TransportInterface|null $transport
 * @method \Sentry\ClientInterface getClient()
 */
class HubFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $clientBuilder = $container->get(ClientBuilderInterface::class);
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

            if (Context::has(ServerRequestInterface::class)) {
                $requestFetcher = $container->get(RequestFetcher::class);
            }

            $integrations[] = new SdkIntegration\RequestIntegration($requestFetcher);

            return array_merge($integrations, $userIntegrations);
        });

        $client = (function () {
            $this->transport = null; // Make the transport is new created before get client
            return $this->getClient();
        })->call($clientBuilder);

        return new Hub($client);
    }

    /**
     * @return SdkIntegration\IntegrationInterface[]
     */
    protected function resolveIntegrationsFromUserConfig(ContainerInterface $container): array
    {
        $integrations = [
            new Integration(),
        ];
        $userIntegrations = $container->get(ConfigInterface::class)->get('sentry.integrations', []);

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
