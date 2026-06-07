# Mail

Mail 元件透過 Symfony Mailer 傳輸傳送檢視、Markdown、HTML 和純文字郵件。

## 安裝

```shell
composer require friendsofhyperf/mail
php bin/hyperf.php vendor:publish friendsofhyperf/mail
```

發布元件會建立 `config/autoload/mail.php`，並將郵件檢視元件複製到 `storage/views/mail`。
如果應用尚無 Hyperf 檢視設定，請另行發布：

```shell
php bin/hyperf.php vendor:publish hyperf/view
```

元件要求 PHP 8.1 或更高版本。部分功能需要可選相依套件：

- `hyperf/devtool` 提供 `gen:mail` 指令。
- `aws/aws-sdk-php` 是 `ses` 和 `ses-v2` 傳輸的必要相依套件。
- Symfony API 郵件傳輸需要 `symfony/http-client`。
- `symfony/mailgun-mailer` 和 `symfony/postmark-mailer` 提供對應傳輸。

## 設定

發布的設定預設使用 `log` mailer。透過 `MAIL_MAILER` 選擇 mailer，並在 `mail.mailers` 下設定。
支援的傳輸包括 `smtp`、`sendmail`、`mail`、`mailgun`、`ses`、`ses-v2`、`postmark`、`log`、
`array`、`failover` 和 `roundrobin`。可使用 `Mail::extend()` 註冊自訂傳輸。

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

mailer 專屬的 `from`、`reply_to`、`to` 或 `return_path` 會覆寫對應全域地址。全域 `to` 地址還會在
傳送前移除原始 To、Cc 和 Bcc 收件者，適合開發環境使用。

## 建立 Mailable

安裝 `hyperf/devtool` 後，可產生基於檢視的 mailable；使用 `--markdown` 會同時建立 Markdown 範本：

```shell
php bin/hyperf.php gen:mail TestMail
php bin/hyperf.php gen:mail TestMail --markdown
```

`Envelope` 定義地址、主旨、標籤、中繼資料和 Symfony 訊息回呼。`Content` 接受 `view`（或其 `html`
別名）、`text`、`markdown`、`htmlString` 和 `with`。mailable 中宣告的 public 屬性也會公開給檢視。

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

附件還可透過 `Attachment::fromData()`、`fromStorage()` 或 `fromStorageDisk()` 建立。可重複使用的附件
物件可以實作 `FriendsOfHyperf\Mail\Contract\Attachable`。

## 傳送郵件

`Mail::mailer()` 選擇已設定的 mailer；省略參數時使用 `mail.default`。`to()`、`cc()` 和 `bcc()`
傳回待傳送郵件物件，其 `send()` 接受 `FriendsOfHyperf\Mail\Contract\Mailable`。傳送成功時傳回
`SentMessage`；當 `MessageSending` 監聽器中止傳送時傳回 `null`。

```php
use App\Mail\TestMail;
use FriendsOfHyperf\Mail\Facade\Mail;

Mail::mailer('smtp')
    ->to('user@example.com', 'Example User')
    ->cc('team@example.com')
    ->send(new TestMail('Hyperf'));
```

對於無需 mailable 類別的郵件，mailer 還提供 `html()`、`raw()`、`plain()`，以及接受檢視名稱或檢視陣列的
`send()`。回呼會收到 `FriendsOfHyperf\Mail\Message`，其未知方法會轉送給底層 Symfony `Email`。

```php
use FriendsOfHyperf\Mail\Facade\Mail;
use FriendsOfHyperf\Mail\Message;

Mail::html('<h1>Hello</h1>', function (Message $message) {
    $message->to('user@example.com')->subject('Greeting');
});
```
