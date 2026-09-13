<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Mail\Mailable;
use FriendsOfHyperf\Mail\Mailable\Content;
use FriendsOfHyperf\Mail\Mailer;
use FriendsOfHyperf\Mail\Message;
use FriendsOfHyperf\Mail\Transport\ArrayTransport;
use FriendsOfHyperf\Support\HtmlString;
use Hyperf\ViewEngine\Contract\FactoryInterface as Factory;
use Hyperf\ViewEngine\Contract\Htmlable;
use Mockery as m;

it('sends HTML and text strings without view callbacks', function () {
    $view = m::mock(Factory::class);
    $view->shouldNotReceive('make');
    $mailer = new Mailer('array', $view, new ArrayTransport());

    $sentMessage = $mailer->send(
        ['html' => new HtmlString('<p>Hello Hyperf</p>'), 'text' => new HtmlString('Hello World')],
        [],
        function (Message $message) {
            $message->to('recipient@example.com')->from('sender@example.com');
        }
    );

    $this->assertSame('<p>Hello Hyperf</p>', $sentMessage->getOriginalMessage()->getHtmlBody());
    $this->assertSame('Hello World', $sentMessage->getOriginalMessage()->getTextBody());
});

it('sends Htmlable content without rendering a view', function () {
    $view = m::mock(Factory::class);
    $view->shouldNotReceive('make');
    $mailer = new Mailer('array', $view, new ArrayTransport());
    $html = m::mock(Htmlable::class);
    $html->expects('toHtml')->andReturn('<p>Hello Hyperf</p>');
    $text = m::mock(Htmlable::class);
    $text->expects('toHtml')->andReturn('Hello World');

    $sentMessage = $mailer->send(['html' => $html, 'text' => $text], [], function (Message $message) {
        $message->to('recipient@example.com')->from('sender@example.com');
    });

    $this->assertSame('<p>Hello Hyperf</p>', $sentMessage->getOriginalMessage()->getHtmlBody());
    $this->assertSame('Hello World', $sentMessage->getOriginalMessage()->getTextBody());
});

it('renders HTML and text string content', function () {
    $view = m::mock(Factory::class);
    $view->shouldNotReceive('make');
    $mailer = new Mailer('array', $view, new ArrayTransport());

    $this->assertSame('<p>Hello Hyperf</p>', $mailer->render(['html' => new HtmlString('<p>Hello Hyperf</p>')]));
    $this->assertSame('Hello World', $mailer->render(['text' => new HtmlString('Hello World')]));
});

it('sends a mailable with pre-rendered HTML content', function () {
    $view = m::mock(Factory::class);
    $view->shouldNotReceive('make');
    $transport = new ArrayTransport();
    $mailer = new Mailer('array', $view, $transport);
    $mailable = new class extends Mailable {
        public function content(): Content
        {
            return new Content(htmlString: '<h1>Hello</h1><p>Mail body</p>');
        }
    };
    $mailable->from('sender@example.com')->to('recipient@example.com')->subject('Greeting');

    $sentMessage = $mailer->send($mailable);

    $this->assertCount(1, $transport->messages());
    $this->assertSame('<h1>Hello</h1><p>Mail body</p>', $sentMessage->getOriginalMessage()->getHtmlBody());
});
