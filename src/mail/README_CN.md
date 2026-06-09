# Mail

[English](README.md)

Mail 组件通过 Symfony Mailer 传输发送视图、Markdown、HTML 和纯文本邮件。

## 安装

```shell
composer require friendsofhyperf/mail
php bin/hyperf.php vendor:publish friendsofhyperf/mail
```

发布组件会创建 `config/autoload/mail.php`，并将邮件视图组件复制到 `storage/views/mail`。
如果应用尚无 Hyperf 视图配置，请另行发布：

```shell
php bin/hyperf.php vendor:publish hyperf/view
```

组件要求 PHP 8.1 或更高版本。部分功能需要可选依赖：

- `hyperf/devtool` 提供 `gen:mail` 命令。
- `aws/aws-sdk-php` 是 `ses` 和 `ses-v2` 传输的必要依赖。
- Symfony API 邮件传输需要 `symfony/http-client`。
- `symfony/mailgun-mailer` 和 `symfony/postmark-mailer` 提供对应传输。

## 配置

发布的配置默认使用 `log` mailer。通过 `MAIL_MAILER` 选择 mailer，并在 `mail.mailers` 下配置。
支持的传输包括 `smtp`、`sendmail`、`mail`、`mailgun`、`ses`、`ses-v2`、`postmark`、`log`、
`array`、`failover` 和 `roundrobin`。可使用 `Mail::extend()` 注册自定义传输。

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

mailer 专属的 `from`、`reply_to`、`to` 或 `return_path` 会覆盖对应全局地址。全局 `to` 地址还会在
发送前移除原始 To、Cc 和 Bcc 收件人，适合开发环境使用。

## 创建 Mailable

安装 `hyperf/devtool` 后，可生成基于视图的 mailable；使用 `--markdown` 会同时创建 Markdown 模板：

```shell
php bin/hyperf.php gen:mail TestMail
php bin/hyperf.php gen:mail TestMail --markdown
```

`Envelope` 定义地址、主题、标签、元数据和 Symfony 消息回调。`Content` 接受 `view`（或其 `html`
别名）、`text`、`markdown`、`htmlString` 和 `with`。mailable 中声明的 public 属性也会暴露给视图。

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

附件还可通过 `Attachment::fromData()`、`fromStorage()` 或 `fromStorageDisk()` 创建。可复用的附件
对象可以实现 `FriendsOfHyperf\Mail\Contract\Attachable`。

## 发送邮件

`Mail::mailer()` 选择已配置的 mailer；省略参数时使用 `mail.default`。`to()`、`cc()` 和 `bcc()`
返回待发送邮件对象，其 `send()` 接受 `FriendsOfHyperf\Mail\Contract\Mailable`。发送成功时返回
`SentMessage`；当 `MessageSending` 监听器中止发送时返回 `null`。

```php
use App\Mail\TestMail;
use FriendsOfHyperf\Mail\Facade\Mail;

Mail::mailer('smtp')
    ->to('user@example.com', 'Example User')
    ->cc('team@example.com')
    ->send(new TestMail('Hyperf'));
```

对于无需 mailable 类的邮件，mailer 还提供 `html()`、`raw()`、`plain()`，以及接受视图名或视图数组的
`send()`。回调会收到 `FriendsOfHyperf\Mail\Message`，其未知方法会转发给底层 Symfony `Email`。

```php
use FriendsOfHyperf\Mail\Facade\Mail;
use FriendsOfHyperf\Mail\Message;

Mail::html('<h1>Hello</h1>', function (Message $message) {
    $message->to('user@example.com')->subject('Greeting');
});
```
