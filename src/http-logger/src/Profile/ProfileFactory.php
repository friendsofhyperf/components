<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Http\Logger\Profile;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;

use function Hyperf\Support\make;

class ProfileFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $class = $config->get('http_logger.log_profile', DefaultLogProfile::class);

        if (! is_a($class, LogProfile::class, true)) {
            throw new RuntimeException(sprintf('Invalid log profile class %s', $class));
        }

        return make($class);
    }
}
