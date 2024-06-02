<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification\Symfony\Mailer;

use FriendsOfHyperf\Notification\Channel\AbstractSymfonyChannel;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Notifier\Channel\EmailChannel as ChannelEmailChannel;

class EmailChannel extends AbstractSymfonyChannel
{
    public function __construct(
        ContainerInterface $container
    ) {
        $config = $container->get(ConfigInterface::class);
        parent::__construct(
            new ChannelEmailChannel(
                transport: Transport::fromDsn(
                    $config->get('symfony.mail.dsn'),
                    dispatcher: $container->get(EventDispatcherInterface::class),
                    logger: $container->get(StdoutLoggerInterface::class)
                ),
                from: $config->get('symfony.mail.from'),
                envelope: $config->get('symfony.mail.envelope')
            ),
            'email'
        );
    }
}
