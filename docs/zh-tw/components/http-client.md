# HTTP Client

適用於 Hyperf 的 HTTP 客戶端元件，移植自 Laravel，並由 Guzzle 提供底層支援。

## 安裝

```shell
composer require friendsofhyperf/http-client guzzlehttp/guzzle:^7.6
```

`guzzlehttp/guzzle` 是本包建議安裝的依賴，傳送請求時必須可用。無需釋出配置檔案。

## 傳送請求

使用 `Http` 門面傳送 `GET`、`HEAD`、`POST`、`PUT`、`PATCH` 和 `DELETE`
請求。請求體預設以 JSON 格式傳送。

```php
use FriendsOfHyperf\Http\Client\Http;

$response = Http::get('https://example.com/users', [
    'page' => 1,
]);

$response = Http::post('https://example.com/users', [
    'name' => 'Taylor',
]);
```

在 HTTP 方法前鏈式呼叫方法以配置待發送請求：

```php
$response = Http::baseUrl('https://example.com')
    ->withToken('token')
    ->acceptJson()
    ->timeout(10)
    ->connectTimeout(3)
    ->withHeaders(['X-Trace-Id' => 'trace-id'])
    ->withUrlParameters(['user' => 1])
    ->get('/users/{user}', ['active' => true]);
```

使用 `withUrlParameters()` 展開 URI 模板佔位符。使用 `asForm()`、`attach()`
或 `withBody()` 分別傳送表單、多部分或原始請求體：

```php
$response = Http::withUrlParameters(['user' => 1])
    ->get('https://example.com/users/{user}');

$response = Http::asForm()->post('https://example.com/login', [
    'email' => 'taylor@example.com',
]);

$response = Http::attach('avatar', fopen('/path/to/avatar.jpg', 'r'), 'avatar.jpg')
    ->post('https://example.com/users/1/avatar');
```

預設連線超時為 10 秒，請求總超時為 30 秒。可使用 `withOptions()` 傳入其他
Guzzle 請求選項。僅在確實需要停用 TLS 證書驗證時使用 `withoutVerifying()`。

## 重試

`retry()` 的第一個引數可以是總嘗試次數，也可以是以毫秒為單位的退避延遲陣列。
第二個引數可以是固定延遲或閉包。可選的第三個引數決定是否重試失敗響應或連線異常。

```php
use FriendsOfHyperf\Http\Client\PendingRequest;
use Throwable;

$response = Http::retry(
    [100, 200],
    0,
    fn (Throwable $exception, PendingRequest $request) => true,
)->get('https://example.com');
```

預設情況下，重試耗盡後會丟擲異常。將第四個引數設為 `false` 可返回最終失敗響應。

## 響應與異常

請求返回 `FriendsOfHyperf\Http\Client\Response`。常用響應方法包括：

```php
$response->body();
$response->json();
$response->json('user.name');
$response->object();
$response->collect();
$response->fluent();
$response->header('Content-Type');
$response->headers();
$response->status();
$response->effectiveUri();

$response->successful();
$response->redirect();
$response->failed();
$response->clientError();
$response->serverError();
```

HTTP 4xx 和 5xx 響應預設不會丟擲異常。在待發送請求或響應上呼叫 `throw()` 可丟擲
`RequestException`。連線失敗會丟擲 `ConnectionException`。

```php
use FriendsOfHyperf\Http\Client\RequestException;

try {
    $response = Http::get('https://example.com/users/1')->throw();
} catch (RequestException $exception) {
    $response = $exception->response;
}
```

## 併發請求

使用 `pool()` 併發傳送請求。使用 `as()` 為響應指定鍵名。

```php
use FriendsOfHyperf\Http\Client\Http;
use FriendsOfHyperf\Http\Client\Pool;

$responses = Http::pool(function (Pool $pool) {
    return [
        $pool->as('user')->get('https://example.com/users/1'),
        $pool->as('posts')->get('https://example.com/posts'),
    ];
});

$responses['user']->status();
```

## 測試

`fake()` 會攔截匹配的請求，並記錄請求以供斷言。呼叫 `preventStrayRequests()`
可拒絕沒有匹配假響應的請求。

```php
use FriendsOfHyperf\Http\Client\Http;
use FriendsOfHyperf\Http\Client\Request;

Http::preventStrayRequests();
Http::fake([
    'example.com/users/*' => Http::response(['name' => 'Taylor'], 200),
]);

$response = Http::get('https://example.com/users/1');

Http::assertSent(fn (Request $request) => $request->url() === 'https://example.com/users/1');
Http::assertSentCount(1);
```

重複請求需要返回不同響應時，可使用響應序列。序列耗盡後預設丟擲
`OutOfBoundsException`，除非配置了 `whenEmpty()` 或 `dontFailWhenEmpty()`。

```php
Http::fakeSequence('example.com/*')
    ->push(['result' => 'first'])
    ->pushStatus(404);
```

其他可用斷言包括 `assertNotSent()`、`assertNothingSent()`、`assertSentInOrder()`
和 `assertSequencesAreEmpty()`。

## 中介軟體與事件

使用 `withMiddleware()`、`withRequestMiddleware()` 或 `withResponseMiddleware()`
新增單次請求中介軟體。`Factory` 還提供 `globalMiddleware()`、
`globalRequestMiddleware()` 和 `globalResponseMiddleware()`。

使用 PSR 事件分發器建立 `Factory` 時，元件會分發 `RequestSending`、
`ResponseReceived` 和 `ConnectionFailed` 事件。

## Laravel 相容性

本元件 API 基於 Laravel HTTP 客戶端，但實際可用 API 和行為以本元件原始碼為準。
可參閱 [Laravel HTTP Client 文件](https://laravel.com/docs/9.x/http-client)
瞭解更多背景，並在使用前對照本元件確認 API。
