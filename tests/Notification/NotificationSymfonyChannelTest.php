<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Notification;

use FriendsOfHyperf\Notification\Channel\SymfonyChannel;
use FriendsOfHyperf\Notification\Notification;
use FriendsOfHyperf\Notification\Traits\Notifiable;
use Hyperf\Di\Container;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Notifier\Recipient\RecipientInterface;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\Group('notification')]
class NotificationSymfonyChannelTest extends TestCase
{
    public function testPayloadToArray(): void
    {
        $config = m::mock(ContainerInterface::class);
        $config->allows('get')->andReturn([]);
        $container = m::mock(Container::class);
        $notifier = m::mock(NotifierInterface::class);
        $container->allows('has')->once()->andReturn(true);
        $container->allows('get')->once()->with(NotifierInterface::class)->andReturn($notifier);
        $notifier->allows('send')->andReturnUsing(function (\Symfony\Component\Notifier\Notification\Notification $notification, RecipientInterface ...$recipients) {
            $this->assertEquals('subject', $notification->getSubject());
            $this->assertEquals(['test'], $notification->getChannels(new Recipient('zds@qq.com', '123123')));
        });
        $channel = new SymfonyChannel($container);
        $notification = new SymfonyNotificationStub();
        $channel->send(new Users(), $notification);
    }
}

class SymfonyNotificationStub extends Notification
{
    public function toSymfony(Users $notifiable): \Symfony\Component\Notifier\Notification\Notification
    {
        return new \Symfony\Component\Notifier\Notification\Notification(
            'subject',
            ['test']
        );
    }

    public function toRecipient(Users $notifiable): RecipientInterface
    {
        return $notifiable->routeNotificationFor();
    }
}

class Users
{
    use Notifiable;

    // 通知手机号
    public function routeNotificationFor(): Recipient
    {
        return new Recipient('zds@qq.com', '123123');
    }
}
