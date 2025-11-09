<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Mail;

use FriendsOfHyperf\Mail\Event\MessageSending;
use FriendsOfHyperf\Mail\Event\MessageSent;
use FriendsOfHyperf\Mail\Mailable;
use FriendsOfHyperf\Mail\Mailer;
use FriendsOfHyperf\Mail\Message;
use FriendsOfHyperf\Mail\Transport\ArrayTransport;
use FriendsOfHyperf\Support\HtmlString;
use Hyperf\ViewEngine\Contract\FactoryInterface as Factory;
use Hyperf\ViewEngine\Contract\ViewInterface;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface as Dispatcher;

use function Hyperf\Collection\collect;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\Group('mail')]
class MailMailerTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testMailerSendSendsMessageWithProperViewContent()
    {
        $view = m::mock(Factory::class);
        $viewInterface = m::mock(ViewInterface::class);
        $view->expects('make')->once()->andReturns($viewInterface);
        $viewInterface->expects('render')->andReturns('rendered.view');

        $mailer = new Mailer('array', $view, new ArrayTransport());

        $sentMessage = $mailer->send('foo', ['data'], function (Message $message) {
            $message->to('taylor@laravel.com')->from('hello@laravel.com');
        });

        $this->assertStringContainsString('rendered.view', $sentMessage->toString());
    }

    public function testMailerSendSendsMessageWithCcAndBccRecipients()
    {
        $view = m::mock(Factory::class);
        $viewInterface = m::mock(ViewInterface::class);
        $view->expects('make')->once()->andReturns($viewInterface);
        $viewInterface->expects('render')->andReturns('rendered.view');

        $mailer = new Mailer('array', $view, new ArrayTransport());

        $sentMessage = $mailer->send('foo', ['data'], function (Message $message) {
            $message->to('taylor@laravel.com')
                ->cc('dries@laravel.com')
                ->bcc('james@laravel.com')
                ->from('hello@laravel.com');
        });

        $recipients = collect($sentMessage->getEnvelope()->getRecipients())->map(function ($recipient) {
            return $recipient->getAddress();
        });

        $this->assertStringContainsString('rendered.view', $sentMessage->toString());
        $this->assertStringContainsString('dries@laravel.com', $sentMessage->toString());
        $this->assertStringNotContainsString('james@laravel.com', $sentMessage->toString());
        $this->assertTrue($recipients->contains('james@laravel.com'));
    }

    public function testMailerSendSendsMessageWithProperViewContentUsingHtmlStrings()
    {
        $view = m::mock(Factory::class);
        $mailer = new Mailer('array', $view, new ArrayTransport());

        $sentMessage = $mailer->send(
            ['html' => fn () => new HtmlString('<p>Hello Laravel</p>'), 'text' => fn () => new HtmlString('Hello World')],
            ['data'],
            function (Message $message) {
                $message->to('taylor@laravel.com')->from('hello@laravel.com');
            }
        );

        $this->assertStringContainsString('<p>Hello Laravel</p>', $sentMessage->toString());
        $this->assertStringContainsString('Hello World', $sentMessage->toString());
    }

    public function testMailerSendSendsMessageWithProperViewContentUsingStringCallbacks()
    {
        $view = m::mock(Factory::class);

        $mailer = new Mailer('array', $view, new ArrayTransport());

        $sentMessage = $mailer->send(
            [
                'html' => function ($data) {
                    $this->assertInstanceOf(Message::class, $data['message']);

                    return new HtmlString('<p>Hello Laravel</p>');
                },
                'text' => function ($data) {
                    $this->assertInstanceOf(Message::class, $data['message']);

                    return new HtmlString('Hello World');
                },
            ],
            [],
            function (Message $message) {
                $message->to('taylor@laravel.com')->from('hello@laravel.com');
            }
        );

        $this->assertStringContainsString('<p>Hello Laravel</p>', $sentMessage->toString());
        $this->assertStringContainsString('Hello World', $sentMessage->toString());
    }

    public function testMailerSendSendsMessageWithProperViewContentUsingHtmlMethod()
    {
        $view = m::mock(Factory::class);
        $mailer = new Mailer('array', $view, new ArrayTransport());

        $sentMessage = $mailer->html('<p>Hello World</p>', function (Message $message) {
            $message->to('taylor@laravel.com')->from('hello@laravel.com');
        });

        $this->assertStringContainsString('<p>Hello World</p>', $sentMessage->toString());
    }

    public function testMailerSendSendsMessageWithProperPlainViewContent()
    {
        $view = m::mock(Factory::class);
        $viewInterface = m::mock(ViewInterface::class);
        $view->expects('make')->twice()->andReturns($viewInterface);
        $viewInterface->expects('render')->twice()->andReturns('rendered.view');

        $mailer = new Mailer('array', $view, new ArrayTransport());

        $sentMessage = $mailer->send(['foo', 'bar'], ['data'], function (Message $message) {
            $message->to('taylor@laravel.com')->from('hello@laravel.com');
        });
        $this->assertStringContainsString('rendered.view', $sentMessage->toString());
        $this->assertStringContainsString('Content-Transfer-Encoding: quoted-printable', $sentMessage->toString());
        $this->assertStringContainsString('Content-Type: text/html; charset=utf-8', $sentMessage->toString());
    }

    public function testMailerSendSendsMessageWithProperPlainViewContentWhenExplicit()
    {
        $view = m::mock(Factory::class);
        $viewInterface = m::mock(ViewInterface::class);
        $view->expects('make')->twice()->andReturns($viewInterface);
        $viewInterface->expects('render')->times(2)->andReturns('rendered.view', 'rendered.plain');
        $mailer = new Mailer('array', $view, new ArrayTransport());

        $sentMessage = $mailer->send(['html' => 'foo', 'text' => 'bar'], ['data'], function (Message $message) {
            $message->to('taylor@laravel.com')->from('hello@laravel.com');
        });
        $this->assertStringContainsStringIgnoringCase('rendered.view', $sentMessage->toString());
        $this->assertStringContainsStringIgnoringCase('rendered.plain', $sentMessage->toString());
    }

    public function testToAllowsEmailAndName()
    {
        $view = m::mock(Factory::class);
        $viewInterface = m::mock(ViewInterface::class);
        $view->expects('make')->andReturns($viewInterface);
        $viewInterface->expects('render')->andReturns('rendered.view');
        $mailer = new Mailer('array', $view, new ArrayTransport());

        $sentMessage = $mailer->to('taylor@laravel.com', 'Taylor Otwell')->send(new TestMail());

        $recipients = $sentMessage->getEnvelope()->getRecipients();
        $this->assertCount(1, $recipients);
        $this->assertSame('taylor@laravel.com', $recipients[0]->getAddress());
        $this->assertSame('Taylor Otwell', $recipients[0]->getName());
    }

    public function testGlobalFromIsRespectedOnAllMessages()
    {
        $view = m::mock(Factory::class);
        $viewInterface = m::mock(ViewInterface::class);
        $view->expects('make')->andReturns($viewInterface);
        $viewInterface->expects('render')->andReturns('rendered.view');
        $mailer = new Mailer('array', $view, new ArrayTransport());
        $mailer->alwaysFrom('hello@laravel.com');

        $sentMessage = $mailer->send('foo', ['data'], function (Message $message) {
            $message->to('taylor@laravel.com');
        });

        $this->assertSame('taylor@laravel.com', $sentMessage->getEnvelope()->getRecipients()[0]->getAddress());
        $this->assertSame('hello@laravel.com', $sentMessage->getEnvelope()->getSender()->getAddress());
    }

    public function testGlobalReplyToIsRespectedOnAllMessages()
    {
        $view = m::mock(Factory::class);
        $viewInterface = m::mock(ViewInterface::class);
        $view->expects('make')->andReturns($viewInterface);
        $viewInterface->expects('render')->andReturns('rendered.view');
        $mailer = new Mailer('array', $view, new ArrayTransport());
        $mailer->alwaysReplyTo('taylor@laravel.com', 'Taylor Otwell');

        $sentMessage = $mailer->send('foo', ['data'], function (Message $message) {
            $message->to('dries@laravel.com')->from('hello@laravel.com');
        });

        $this->assertSame('dries@laravel.com', $sentMessage->getEnvelope()->getRecipients()[0]->getAddress());
        $this->assertStringContainsString('Reply-To: Taylor Otwell <taylor@laravel.com>', $sentMessage->toString());
    }

    public function testGlobalToIsRespectedOnAllMessages()
    {
        $view = m::mock(Factory::class);
        $viewInterface = m::mock(ViewInterface::class);
        $view->expects('make')->andReturns($viewInterface);
        $viewInterface->expects('render')->andReturns('rendered.view');
        $mailer = new Mailer('array', $view, new ArrayTransport());
        $mailer->alwaysTo('taylor@laravel.com', 'Taylor Otwell');

        $sentMessage = $mailer->send('foo', ['data'], function (Message $message) {
            $message->from('hello@laravel.com');
            $message->to('nuno@laravel.com');
            $message->cc('dries@laravel.com');
            $message->bcc('james@laravel.com');
        });

        $recipients = collect($sentMessage->getEnvelope()->getRecipients())->map(function ($recipient) {
            return $recipient->getAddress();
        });

        $this->assertSame('taylor@laravel.com', $sentMessage->getEnvelope()->getRecipients()[0]->getAddress());
        $this->assertDoesNotMatchRegularExpression('/^To: nuno@laravel.com/m', $sentMessage->toString());
        $this->assertDoesNotMatchRegularExpression('/^Cc: dries@laravel.com/m', $sentMessage->toString());
        $this->assertMatchesRegularExpression('/^X-To: nuno@laravel.com/m', $sentMessage->toString());
        $this->assertMatchesRegularExpression('/^X-Cc: dries@laravel.com/m', $sentMessage->toString());
        $this->assertMatchesRegularExpression('/^X-Bcc: james@laravel.com/m', $sentMessage->toString());
        $this->assertFalse($recipients->contains('nuno@laravel.com'));
        $this->assertFalse($recipients->contains('dries@laravel.com'));
        $this->assertFalse($recipients->contains('james@laravel.com'));
    }

    public function testGlobalReturnPathIsRespectedOnAllMessages()
    {
        $view = m::mock(Factory::class);
        $viewInterface = m::mock(ViewInterface::class);
        $view->expects('make')->andReturns($viewInterface);
        $viewInterface->expects('render')->andReturns('rendered.view');

        $mailer = new Mailer('array', $view, new ArrayTransport());
        $mailer->alwaysReturnPath('taylorotwell@gmail.com');

        $sentMessage = $mailer->send('foo', ['data'], function (Message $message) {
            $message->to('taylor@laravel.com')->from('hello@laravel.com');
        });

        $this->assertStringContainsString('Return-Path: <taylorotwell@gmail.com>', $sentMessage->toString());
    }

    public function testEventsAreDispatched()
    {
        $view = m::mock(Factory::class);
        $viewInterface = m::mock(ViewInterface::class);
        $view->expects('make')->once()->andReturns($viewInterface);
        $viewInterface->expects('render')->andReturns('rendered.view');

        $events = m::mock(Dispatcher::class);
        $events->allows('dispatch')->once()->andReturnUsing(function ($e) {
            if ($e instanceof MessageSending) {
                $e->setShouldSend(false);
            }
            if ($e instanceof MessageSent) {
            }
            return $e;
        });

        $mailer = new Mailer('array', $view, new ArrayTransport(), $events);

        $mailer->send('foo', ['data'], function (Message $message) {
            $message->to('taylor@laravel.com')->from('hello@laravel.com');
        });
        $this->assertTrue(true);
    }

    public function testMacroable()
    {
        Mailer::macro('foo', function () {
            return 'bar';
        });

        $mailer = new Mailer('array', m::mock(Factory::class), new ArrayTransport());

        $this->assertSame(
            'bar',
            $mailer->foo()
        );
    }
}

class TestMail extends Mailable
{
    public function build()
    {
        return $this->view('view')
            ->from('hello@laravel.com');
    }
}
