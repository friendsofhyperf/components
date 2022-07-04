<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\HttpLogger\Writer;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

class WriterFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $class = $config->get('http_logger.log_writer', DefaultLogWriter::class);

        return make($class);
    }
}
