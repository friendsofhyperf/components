<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\Transport;

use function Hyperf\Support\env;

return [
    'transport' => static function (ContainerInterface $container) {
        return Transport::fromDsn(
            dsn: env('MAIL_DSN'),
            dispatcher: $container->get(EventDispatcherInterface::class),
            logger: $container->get(StdoutLoggerInterface::class)
        );
    },
    'from' => env('MAIL_FROM', 'Hyperf'),
    'envelope' => null,
];
