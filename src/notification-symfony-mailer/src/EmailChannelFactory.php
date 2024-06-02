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
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Notifier\Channel\EmailChannel;

class EmailChannelFactory
{
    public function __construct(
        private readonly ConfigInterface $config,
        private readonly ContainerInterface $container
    ) {
    }

    public function __invoke(): EmailChannel
    {
        $transportClosure = $this->config->get('symfony.email.transport');
        if (! is_callable($transportClosure)) {
            throw new InvalidArgumentException('symfony.email.transport must be a callable.');
        }
        $transport = $transportClosure($this->container);
        return new EmailChannel(
            transport: $transport,
            from: $this->config->get('symfony.email.from'),
            envelope: $this->config->get('symfony.email.envelope'),
        );
    }
}
