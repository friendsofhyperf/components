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

use FriendsOfHyperf\Notification\Contract\Channel;
use FriendsOfHyperf\Notification\Notification;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Notifier\Channel\EmailChannel as Base;

class EmailChannel extends Base implements Channel
{
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        StdoutLoggerInterface $stdoutLogger,
        ConfigInterface $config
    ) {
        parent::__construct(
            transport: Transport::fromDsn($config->get('symfony.mail.dsn'), dispatcher: $eventDispatcher, logger: $stdoutLogger),
            from: $config->get('symfony.mail.from')
        );
    }

    public function send(mixed $notifiable, Notification $notification, ?string $transportName = null): mixed
    {
        $this->notify($notification, $notifiable, $transportName);
        return true;
    }
}
