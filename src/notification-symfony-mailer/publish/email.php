<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Transport;

use function Hyperf\Support\env;

return [
    'transport' => static function (EventDispatcherInterface $dispatcher, LoggerInterface $logger) {
        return Transport::fromDsn(dsn: env('MAIL_DSN'), dispatcher: $dispatcher, logger: $logger);
    },
    'from' => env('MAIL_FROM', 'Hyperf'),
    'envelope' => null,
];
