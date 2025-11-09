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

use FriendsOfHyperf\Notification\Channel\DatabaseChannel;
use FriendsOfHyperf\Tests\Notification\Stubs\ExtendedDatabaseChannel;
use FriendsOfHyperf\Tests\Notification\Stubs\NotificationDatabaseChannelCustomizeTypeTestNotification;
use FriendsOfHyperf\Tests\Notification\Stubs\NotificationDatabaseChannelTestNotification;
use Hyperf\Database\Model\Model;
use Mockery as m;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Group('notification')]
class NotificationDatabaseChannelTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testDatabaseChannelCreatesDatabaseRecordWithProperData()
    {
        $notification = new NotificationDatabaseChannelTestNotification();
        $notification->id = '1';
        $notifiable = m::mock();

        $notifiable->shouldReceive('routeNotificationFor->create')->andReturnUsing(function ($data) {
            $this->assertIsArray($data);
            $this->assertSame(NotificationDatabaseChannelTestNotification::class, $data['type']);
            $this->assertSame('Hello World', $data['data']['message']);
            $this->assertNull($data['read_at']);
            return m::mock(Model::class);
        });

        $channel = new DatabaseChannel();
        $channel->send($notifiable, $notification);
    }

    public function testCorrectPayloadIsSentToDatabase()
    {
        $notification = new NotificationDatabaseChannelTestNotification();
        $notification->id = '1';
        $notifiable = m::mock();

        $notifiable->shouldReceive('routeNotificationFor->create')->andReturnUsing(function ($data) {
            $this->assertIsArray($data);
            $this->assertSame(NotificationDatabaseChannelTestNotification::class, $data['type']);
            $this->assertSame('Hello World', $data['data']['message']);
            $this->assertNull($data['read_at']);
            $this->assertSame('else', $data['something']);
            return m::mock(Model::class);
        });

        $channel = new ExtendedDatabaseChannel();
        $channel->send($notifiable, $notification);
    }

    public function testCustomizeTypeIsSentToDatabase()
    {
        $notification = new NotificationDatabaseChannelCustomizeTypeTestNotification();
        $notification->id = '1';
        $notifiable = m::mock();

        $notifiable->shouldReceive('routeNotificationFor->create')->withArgs(function ($data) {
            $this->assertIsArray($data);
            $this->assertSame([
                'id' => '1',
                'type' => 'MONTHLY',
                'data' => ['invoice_id' => 1],
                'read_at' => null,
                'something' => 'else',
            ], $data);
            return true;
        })->andReturn(m::mock(Model::class));

        $channel = new ExtendedDatabaseChannel();
        $channel->send($notifiable, $notification);
    }
}
