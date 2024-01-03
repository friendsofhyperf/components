<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\HttpClient;

use FriendsOfHyperf\Sentry\Version;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

class HttpClientFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        return new HttpClient(
            Version::getSdkIdentifier(),
            Version::getSdkVersion(),
            (int) $config->get('sentry.http_chanel_size', 65535),
            (int) $config->get('sentry.http_concurrent_limit', 100)
        );
    }
}
