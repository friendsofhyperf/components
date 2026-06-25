# Mail

Mail 組件通過 Symfony Mailer 傳輸發送視圖、Markdown、HTML 和純文本郵件。

## 安裝

```shell
composer require friendsofhyperf/mail
php bin/hyperf.php vendor:publish friendsofhyperf/mail
```

發佈組件會創建 `config/autoload/mail.php`，並將郵件視圖組件複製到 `storage/views/mail`。
如果應用尚無 Hyperf 視圖配置，請另行發佈：

```shell
php bin/hyperf.php vendor:publish hyperf/view
```

組件要求 PHP 8.1 或更高版本。部分功能需要可選依賴：

- `hyperf/devtool` 提供 `gen:mail` 命令。
- `aws/aws-sdk-php` 是 `ses` 和 `ses-v2` 傳輸的必要依賴。
- Symfony API 郵件傳輸需要 `symfony/http-client`。
- `symfony/mailgun-mailer` 和 `symfony/postmark-mailer` 提供對應傳輸。

## 配置

發佈的配置默認使用 `log` mailer。通過 `MAIL_MAILER` 選擇 mailer，並在 `mail.mailers` 下配置。
支持的傳輸包括 `smtp`、`sendmail`、`mail`、`mailgun`、`ses`、`ses-v2`、`postmark`、`log`、
`array`、`failover` 和 `roundrobin`。可使用 `Mail::extend()` 註冊自定義傳輸。

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

mailer 專屬的 `from`、`reply_to`、`to` 或 `return_path` 會覆蓋對應全局地址。全局 `to` 地址還會在
發送前移除原始 To、Cc 和 Bcc 收件人，適合開發環境使用。

## 創建 Mailable

安裝 `hyperf/devtool` 後，可生成基於視圖的 mailable；使用 `--markdown` 會同時創建 Markdown 模板：

```shell
php bin/hyperf.php gen:mail TestMail
php bin/hyperf.php gen:mail TestMail --markdown
```

`Envelope` 定義地址、主題、標籤、元數據和 Symfony 消息回調。`Content` 接受 `view`（或其 `html`
別名）、`text`、`markdown`、`htmlString` 和 `with`。mailable 中聲明的 public 屬性也會暴露給視圖。

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

附件還可通過 `Attachment::fromData()`、`fromStorage()` 或 `fromStorageDisk()` 創建。可複用的附件
對象可以實現 `FriendsOfHyperf\Mail\Contract\Attachable`。

## 發送郵件

`Mail::mailer()` 選擇已配置的 mailer；省略參數時使用 `mail.default`。`to()`、`cc()` 和 `bcc()`
返回待發送郵件對象，其 `send()` 接受 `FriendsOfHyperf\Mail\Contract\Mailable`。發送成功時返回
`SentMessage`；當 `MessageSending` 監聽器中止發送時返回 `null`。

```php
use App\Mail\TestMail;
use FriendsOfHyperf\Mail\Facade\Mail;

Mail::mailer('smtp')
    ->to('user@example.com', 'Example User')
    ->cc('team@example.com')
    ->send(new TestMail('Hyperf'));
```

對於無需 mailable 類的郵件，mailer 還提供 `html()`、`raw()`、`plain()`，以及接受視圖名或視圖數組的
`send()`。回調會收到 `FriendsOfHyperf\Mail\Message`，其未知方法會轉發給底層 Symfony `Email`。

```php
use FriendsOfHyperf\Mail\Facade\Mail;
use FriendsOfHyperf\Mail\Message;

Mail::html('<h1>Hello</h1>', function (Message $message) {
    $message->to('user@example.com')->subject('Greeting');
});
```
