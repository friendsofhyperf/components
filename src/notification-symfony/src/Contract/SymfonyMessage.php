<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification\Symfony\Contract;

use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\RecipientInterface;

interface SymfonyMessage
{
    public function getNotification(mixed $notifiable): Notification;

    /**
     * @return RecipientInterface|RecipientInterface[]
     */
    public function recipients(mixed $notifiable): RecipientInterface|array;
}
