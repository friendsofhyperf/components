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

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Notifier\Channel\EmailChannel;

class EmailChannelFactory
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ConfigInterface $config,
    ) {
    }

    public function __invoke(): EmailChannel
    {
        return new EmailChannel(
            transport: Transport::fromDsn(
                dsn: $this->config->get('symfony.email.dsn'),
                dispatcher: $this->container->get(EventDispatcherInterface::class),
                logger: $this->container->get(StdoutLoggerInterface::class),
            ),
            from: $this->config->get('symfony.email.from'),
            envelope: $this->config->get('symfony.email.envelope'),
        );
    }
}
