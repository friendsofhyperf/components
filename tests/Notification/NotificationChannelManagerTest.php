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
use FriendsOfHyperf\Notification\Contract\Channel;
use FriendsOfHyperf\Notification\Contract\Dispatcher;
use FriendsOfHyperf\Notification\Event\NotificationSending;
use FriendsOfHyperf\Notification\Event\NotificationSent;
use FriendsOfHyperf\Notification\Notification;
use FriendsOfHyperf\Notification\Traits\Notifiable;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\TranslatorInterface;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSource;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use ReflectionClass;

/**
 * @internal
 * @coversNothing
 */
class NotificationChannelManagerTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
        $reflection = new ReflectionClass(ApplicationContext::class);
        $reflection->getProperty('container')->setValue(null);
    }

    public function testNotificationCanBeDispatchedToDriver()
    {
        $container = new Container(new DefinitionSource([]));

        $container->set('config', ['app.name' => 'Name', 'app.logo' => 'Logo']);
        $container->set(Dispatcher::class, $events = m::mock(EventDispatcherInterface::class));
        $translator = m::mock(TranslatorInterface::class);
        $driver = m::mock(Channel::class);
        $container->set(get_class($driver), $driver);
        ApplicationContext::setContainer($container);
        $manager = new ChannelManager(...[$container, $events, $translator]);
        $reflection = new ReflectionClass($manager);
        $reflection->getProperty('channels')->setValue($manager, ['test' => $driver]);
        $driver->shouldReceive('send')->once();
        $events->shouldReceive('dispatch')->with(m::type(NotificationSending::class));
        $events->shouldReceive('dispatch')->with(m::type(NotificationSent::class));

        $manager->send(new NotificationChannelManagerTestNotifiable(), new NotificationChannelManagerTestNotification());
    }
}

class NotificationChannelManagerTestNotifiable
{
    use Notifiable;
}

class NotificationChannelManagerTestNotification extends Notification
{
    public function via()
    {
        return ['test'];
    }

    public function message()
    {
        return $this->line('test')->action('Text', 'url');
    }
}

class NotificationChannelManagerTestNotificationWithTwoChannels extends Notification
{
    public function via()
    {
        return ['test', 'test2'];
    }

    public function message()
    {
        return $this->line('test')->action('Text', 'url');
    }
}

class NotificationChannelManagerTestCancelledNotification extends Notification
{
    public function via()
    {
        return ['test'];
    }

    public function message()
    {
        return $this->line('test')->action('Text', 'url');
    }

    public function shouldSend($notifiable, $channel)
    {
        return false;
    }
}

class NotificationChannelManagerTestNotCancelledNotification extends Notification
{
    public function via()
    {
        return ['test'];
    }

    public function message()
    {
        return $this->line('test')->action('Text', 'url');
    }

    public function shouldSend($notifiable, $channel)
    {
        return true;
    }
}

class NotificationChannelManagerTestQueuedNotification extends Notification
{
    public function via()
    {
        return ['test'];
    }

    public function message()
    {
        return $this->line('test')->action('Text', 'url');
    }
}
