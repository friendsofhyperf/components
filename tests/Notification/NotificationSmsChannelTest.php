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

use FriendsOfHyperf\Notification\Channel\EasySmsChannel;
use FriendsOfHyperf\Notification\Notification;
use FriendsOfHyperf\Notification\Traits\Notifiable;
use Hyperf\Di\Container;
use Mockery as m;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Message;
use Overtrue\EasySms\PhoneNumber;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class NotificationSmsChannelTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testPayloadToArray(): void
    {
        $config = m::mock(ContainerInterface::class);
        $config->allows('get')->andReturn([]);
        $container = m::mock(Container::class);
        $sms = m::mock(EasySms::class);
        $container->allows('has')->once()->andReturn(true);
        $container->allows('get')->once()->with(EasySms::class)->andReturn($sms);
        $sms->allows('send')->andReturnUsing(function ($phone, $params) {
            $this->assertSame('13800138000', $phone);
            $this->assertSame('content', $params['content']);
            $this->assertSame('template', $params['template']);
            $this->assertSame(['data'], $params['data']);
        });
        $channel = new EasySmsChannel($container);
        $notification = new SmsNotificationToArrayStub('content', 'template', ['data']);
        $channel->send(new User(), $notification);
    }

    public function testPayloadToSmsMessage(): void
    {
        $config = m::mock(ContainerInterface::class);
        $config->allows('get')->andReturn([]);
        $container = m::mock(Container::class);
        $sms = m::mock(EasySms::class);
        $container->allows('has')->once()->andReturn(true);
        $container->allows('get')->once()->with(EasySms::class)->andReturn($sms);
        $sms->allows('send')->andReturnUsing(function ($phone, $params) {
            $this->assertInstanceOf(Message::class, $params);
            $this->assertSame('13800138000', $phone);
            $this->assertSame('content', $params->getContent());
            $this->assertSame('template', $params->getTemplate());
            $this->assertSame(['xxx'], $params->getData());
        });
        $channel = new EasySmsChannel($container);
        $notification = new SmsNotificationToSmsMessageStub('content', 'template', ['data']);
        $channel->send(new User(), $notification);
    }

    public function testPayloadToSms(): void
    {
        $config = m::mock(ContainerInterface::class);
        $config->allows('get')->andReturn([]);
        $container = m::mock(Container::class);
        $sms = m::mock(EasySms::class);
        $container->allows('has')->once()->andReturn(true);
        $container->allows('get')->once()->with(EasySms::class)->andReturn($sms);
        $sms->allows('send')->andReturnUsing(function ($phone, $params) {
            $this->assertInstanceOf(Message::class, $params);
            /*
             * @var Message $params
             */
            $this->assertSame('13800138000', $phone);
            $this->assertSame('content', $params->getContent());
            $this->assertSame('template', $params->getTemplate());
            $this->assertSame(['data'], $params->getData());
        });
        $channel = new EasySmsChannel($container);
        $notification = new SmsNotificationToSmsStub('content', 'template', ['data']);
        $channel->send(new User(), $notification);
    }
}

class SmsNotificationToSmsStub extends Notification
{
    public function __construct(
        private string $content,
        private string $template,
        private array $data
    ) {
    }

    public function via()
    {
        return [
            'sms',
        ];
    }

    public function toSms(): array
    {
        return [
            'content' => $this->content,
            'template' => $this->template,
            'data' => $this->data,
        ];
    }
}
class SmsNotificationToSmsMessageStub extends Notification
{
    public function __construct(
        private string $content,
        private string $template,
        private array $data
    ) {
    }

    public function via()
    {
        return [
            'sms',
        ];
    }

    public function toSmsMessage(): Message
    {
        $message = new Message();
        $message->setData(['xxx']);
        $message->setContent($this->content);
        $message->setTemplate($this->template);
        $message->setType('xxx');
        $message->setGateways(['xxx']);
        return $message;
    }
}

class SmsNotificationToArrayStub extends Notification
{
    public function __construct(
        private string $content,
        private string $template,
        private array $data
    ) {
    }

    public function via()
    {
        return [
            'sms',
        ];
    }

    public function toArray()
    {
        return [
            'content' => $this->content,
            'template' => $this->template,
            'data' => $this->data,
        ];
    }
}

class User
{
    use Notifiable;

    // 通知手机号
    public function routeNotificationForSms(): string|PhoneNumber
    {
        return '13800138000';
    }
}
