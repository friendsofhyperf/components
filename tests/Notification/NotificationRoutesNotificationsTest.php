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

use FriendsOfHyperf\Notification\ChannelManager;
use FriendsOfHyperf\Notification\Contract\Dispatcher;
use FriendsOfHyperf\Notification\Notification;
use FriendsOfHyperf\Tests\Notification\Stubs\RoutesNotificationsTestInstance;
use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSource;
use Mockery as m;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @internal
 */
#[Group('notification')]
class NotificationRoutesNotificationsTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
        $reflection = new ReflectionClass(ApplicationContext::class);
        $reflection->getProperty('container')->setValue(null);
    }

    public function testNotificationCanBeDispatched()
    {
        $container = new Container(new DefinitionSource([]));
        $factory = m::mock(ChannelManager::class);
        $container->set(Dispatcher::class, $factory);
        $notifiable = new RoutesNotificationsTestInstance();
        $instance = new class extends Notification {};
        $factory->shouldReceive('send')->andReturnUsing(function ($originNotifiable, $instance) use ($notifiable) {
            $this->assertEquals($notifiable, $originNotifiable);
            $this->assertInstanceOf(Notification::class, $instance);
        });
        ApplicationContext::setContainer($container);
        $notifiable->notify($instance);
    }

    public function testNotificationOptionRouting(): void
    {
        $instance = new RoutesNotificationsTestInstance();
        $this->assertSame('bar', $instance->routeNotificationFor('foo'));
        $this->assertSame('taylor@laravel.com', $instance->routeNotificationFor('mail'));
    }
}
