# Mail

The mail component sends view, Markdown, HTML, and plain-text messages through Symfony Mailer
transports.

## Installation

```shell
composer require friendsofhyperf/mail
php bin/hyperf.php vendor:publish friendsofhyperf/mail
```

Publishing the package creates `config/autoload/mail.php` and copies the mail view components to
`storage/views/mail`. If your application does not already have the Hyperf view configuration,
publish it separately:

```shell
php bin/hyperf.php vendor:publish hyperf/view
```

The package requires PHP 8.1 or later. Some features require optional packages:

- `hyperf/devtool` provides the `gen:mail` command.
- `aws/aws-sdk-php` is required for the `ses` and `ses-v2` transports.
- `symfony/http-client` is required for Symfony API mail transports.
- `symfony/mailgun-mailer` and `symfony/postmark-mailer` provide the corresponding transports.

## Configuration

The published configuration defaults to the `log` mailer. Select a mailer with `MAIL_MAILER`, then
configure it under `mail.mailers`. Supported transports are `smtp`, `sendmail`, `mail`, `mailgun`,
`ses`, `ses-v2`, `postmark`, `log`, `array`, `failover`, and `roundrobin`. Custom transports can be
registered with `Mail::extend()`.

```php
// config/autoload/mail.php
use function Hyperf\Support\env;

return [
    'default' => env('MAIL_MAILER', 'log'),
    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
            'scheme' => env('MAIL_SCHEME', 'smtp'),
        ],
        'log' => [
            'transport' => 'log',
            'group' => env('MAIL_LOG_GROUP', 'default'),
            'name' => env('MAIL_LOG_NAME', 'mail'),
        ],
    ],
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Example'),
    ],
    'markdown' => [
        'theme' => env('MAIL_MARKDOWN_THEME', 'default'),
        'paths' => [
            BASE_PATH . '/storage/views/mail',
        ],
    ],
];
```

A mailer-specific `from`, `reply_to`, `to`, or `return_path` value overrides the corresponding
global address. A global `to` address also removes the original To, Cc, and Bcc recipients before
sending, which is useful in development.

## Creating a Mailable

With `hyperf/devtool` installed, generate a view-based mailable or use `--markdown` to also create a
Markdown template:

```shell
php bin/hyperf.php gen:mail TestMail
php bin/hyperf.php gen:mail TestMail --markdown
```

`Envelope` defines addresses, subject, tags, metadata, and Symfony message callbacks. `Content`
accepts `view` (or its `html` alias), `text`, `markdown`, `htmlString`, and `with`. Public properties
declared by your mailable are also exposed to the view.

```php
namespace App\Mail;

use FriendsOfHyperf\Mail\Mailable;
use FriendsOfHyperf\Mail\Mailable\Attachment;
use FriendsOfHyperf\Mail\Mailable\Content;
use FriendsOfHyperf\Mail\Mailable\Envelope;

class TestMail extends Mailable
{
    public function __construct(public readonly string $name)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Test Mail');
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.test',
            with: ['name' => $this->name],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath(BASE_PATH . '/storage/report.pdf')
                ->as('report.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
```

Attachments can also be created with `Attachment::fromData()`, `fromStorage()`, or
`fromStorageDisk()`. A reusable attachable object may implement
`FriendsOfHyperf\Mail\Contract\Attachable`.

## Sending Mail

`Mail::mailer()` selects a configured mailer; omitting its argument uses `mail.default`. The
`to()`, `cc()`, and `bcc()` methods return a pending mail object whose `send()` method accepts a
`FriendsOfHyperf\Mail\Contract\Mailable`. Sending returns a `SentMessage` or `null` when a
`MessageSending` listener stops delivery.

```php
use App\Mail\TestMail;
use FriendsOfHyperf\Mail\Facade\Mail;

Mail::mailer('smtp')
    ->to('user@example.com', 'Example User')
    ->cc('team@example.com')
    ->send(new TestMail('Hyperf'));
```

For messages that do not need a mailable class, the mailer also exposes `html()`, `raw()`,
`plain()`, and `send()` with a view name or view array. The callback receives a
`FriendsOfHyperf\Mail\Message`, which forwards unknown methods to the underlying Symfony
`Email`.

```php
use FriendsOfHyperf\Mail\Facade\Mail;
use FriendsOfHyperf\Mail\Message;

Mail::html('<h1>Hello</h1>', function (Message $message) {
    $message->to('user@example.com')->subject('Greeting');
});
```
