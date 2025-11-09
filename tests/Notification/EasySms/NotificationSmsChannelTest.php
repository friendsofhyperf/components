<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Notification\EasySms;

use FriendsOfHyperf\Notification\EasySms\Channel\EasySmsChannel;
use FriendsOfHyperf\Notification\EasySms\Contract\Smsable;
use FriendsOfHyperf\Notification\EasySms\EasySms;
use FriendsOfHyperf\Notification\Notification;
use FriendsOfHyperf\Notification\Traits\Notifiable;
use Mockery as m;
use Overtrue\EasySms\Message;
use Overtrue\EasySms\PhoneNumber;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 */
class NotificationSmsChannelTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testPayloadToSmsMessage(): void
    {
        $config = m::mock(ContainerInterface::class);
        $config->allows('get')->andReturn([]);
        $sms = m::mock(EasySms::class);
        $sms->allows('send')->andReturnUsing(function ($phone, $params) {
            $this->assertInstanceOf(Message::class, $params);
            $this->assertSame('13800138000', $phone);
            $this->assertSame('content', $params->getContent());
            $this->assertSame('template', $params->getTemplate());
            $this->assertSame(['xxx'], $params->getData());
            return [];
        });
        $channel = new EasySmsChannel($sms);
        $notification = new SmsNotificationToSmsMessageStub('content', 'template', ['data']);
        $channel->send(new User(), $notification);
    }

    public function testPayloadToSms(): void
    {
        $config = m::mock(ContainerInterface::class);
        $config->allows('get')->andReturn([]);
        $sms = m::mock(EasySms::class);
        $sms->allows('send')->andReturnUsing(function ($phone, $params) {
            /*
             * @var Message $params
             */
            $this->assertSame('13800138000', $phone);
            $this->assertSame('content', $params['content']);
            $this->assertSame('template', $params['template']);
            $this->assertSame(['data'], $params['data']);
            return [];
        });
        $channel = new EasySmsChannel($sms);
        $notification = new SmsNotificationToSmsStub('content', 'template', ['data']);
        $channel->send(new User(), $notification);
    }
}

class SmsNotificationToSmsStub extends Notification implements Smsable
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

    public function toSms(mixed $notifiable): array|Message
    {
        return [
            'content' => $this->content,
            'template' => $this->template,
            'data' => $this->data,
        ];
    }
}
class SmsNotificationToSmsMessageStub extends Notification implements Smsable
{
    public function __construct(
        private string $content,
        private string $template,
    ) {
    }

    public function via()
    {
        return [
            'sms',
        ];
    }

    public function toSms(mixed $notifiable): array|Message
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
class User
{
    use Notifiable;

    // 通知手机号
    public function routeNotificationForSms(): string|PhoneNumber
    {
        return '13800138000';
    }
}
