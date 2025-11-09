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

use FriendsOfHyperf\Notification\AnonymousNotifiable;
use FriendsOfHyperf\Notification\ChannelManager;
use FriendsOfHyperf\Notification\NotificationSender;
use FriendsOfHyperf\Tests\Notification\Stubs\DummyNotificationWithDatabaseVia;
use FriendsOfHyperf\Tests\Notification\Stubs\DummyNotificationWithEmptyStringVia;
use Hyperf\Contract\TranslatorInterface;
use Hyperf\Event\EventDispatcher;
use Mockery as m;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Group('notification')]
class NotificationSenderTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        m::close();
    }

    public function testItCanSendNotificationsWithAnEmptyStringVia(): void
    {
        $notifiable = new AnonymousNotifiable();
        $manager = m::mock(ChannelManager::class);
        $events = m::mock(EventDispatcher::class);
        $translator = m::mock(TranslatorInterface::class);
        $events->allows('dispatch')->with(m::type('FriendsOfHyperf\Notification\Event\NotificationSending'));
        $manager->allows('channel->send');
        $events->allows('dispatch')->with(m::type('FriendsOfHyperf\Notification\Event\NotificationSent'));
        $sender = new NotificationSender($manager, $events, $translator);
        $sender->send($notifiable, new DummyNotificationWithEmptyStringVia());
        $this->assertTrue(true);
    }

    public function testItCannotSendNotificationsViaDatabaseForAnonymousNotifiables(): void
    {
        $notifiable = new AnonymousNotifiable();
        $manager = m::mock(ChannelManager::class);
        $events = m::mock(EventDispatcher::class);
        $translator = m::mock(TranslatorInterface::class);
        $sender = new NotificationSender($manager, $events, $translator);
        $sender->send($notifiable, new DummyNotificationWithDatabaseVia());
        $this->assertTrue(true);
    }
}
