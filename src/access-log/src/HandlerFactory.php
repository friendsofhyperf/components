<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\AccessLog;

use FriendsOfHyperf\AccessLog\Formatter\AccessLogFormatter;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;

class HandlerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $name = (string) $config->get('access_log.logger.name', 'access');
        $group = (string) $config->get('access_log.logger.group', 'default');
        $logger = $container->get(LoggerFactory::class)->get($name, $group);

        return new Handler($config, $logger, $container->get(AccessLogFormatter::class));
    }
}
