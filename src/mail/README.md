# Macros

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/mail)](https://packagist.org/packages/friendsofhyperf/mail)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/mail)](https://packagist.org/packages/friendsofhyperf/mail)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/mail)](https://github.com/friendsofhyperf/mail)

Email component of Hyperf

## Installation

- Request

```shell
composer require friendsofhyperf/mail
php bin/hyperf vendor:publish friendsofhyperf/mail
# Publish the view configuration file.
php bin/hyperf vendor:publish hyperf/view 

```

## Usage

###

```php
// config/autoload/mail.php
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use function Hyperf\Support\env;

return [
    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send all email
    | messages unless another mailer is explicitly specified when sending
    | the message. All additional mailers can be configured within the
    | "mailers" array. Examples of each type of mailer are provided.
    |
    */

    'default' => env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Laravel supports a variety of mail "transport" drivers that can be used
    | when delivering an email. You may specify which one you're using for
    | your mailers below. You may also add additional mailers if needed.
    |
    | Supported: "smtp", "sendmail", "mailgun", "ses", "ses-v2",
    |            "postmark", "resend", "log", "array",
    |            "failover", "roundrobin"
    |
    */

    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'url' => env('MAIL_URL','smtp://xxx@xxx:xxx@xxx.com:465'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
            // 'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        'resend' => [
            'transport' => 'resend',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'group' => env('MAIL_LOG_GROUP','default'),
            'name' => env('MAIL_LOG_NAME','mail'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all emails sent by your application to be sent from
    | the same address. Here you may specify a name and address that is
    | used globally for all emails that are sent by your application.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hyperf@hyperf.com'),
        'name' => env('MAIL_FROM_NAME', 'Hyperf'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Markdown Mail Settings
    |--------------------------------------------------------------------------
    |
    | If you are using Markdown based email rendering, you may configure your
    | theme and component paths here, allowing you to customize the design
    | of the emails. Or, you may simply stick with the Laravel defaults!
    |
    */

    'markdown' => [
        'theme' => env('MAIL_MARKDOWN_THEME', 'default'),
        'paths' => [
            BASE_PATH . '/storage/views/mail',
        ],
    ],
];
```

### Build a Mail Class

```shell
php bin/hyperf.php gen:mail TestMail
```

```php
// app/Mail/TestMail.php

namespace App\Mail;

use FriendsOfHyperf\Mail\Mailable;
use FriendsOfHyperf\Mail\Mailable\Content;
use FriendsOfHyperf\Mail\Mailable\Envelope;

class TestMail extends Mailable
{

    /**
     * Create a new message instance.
     */
    public function __construct(
        private readonly string $name,
    ){}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Test Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.test',
            with: [
                'name' => $this->name,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Friendsofhyperf\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
```

### Your Controller or Service

```php
// app/Controller/IndexController.php

use FriendsOfHyperf\Mail\Facade\Mail;

class IndexController extends AbstractController
{
    public function index()
    {
        $user = $this->request->input('user', 'Hyperf');
        $mailer = Mail::mailer('smtp');
        $mailer->alwaysFrom('root@imoi.cn','Hyperf');

        $mailer->to('2771717608@qq.com')->send(new \App\Mail\TestMail($user));
        $method = $this->request->getMethod();

        return [
            'method' => $method,
            'message' => "Hello {$user}.",
        ];
    }
}

```

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
