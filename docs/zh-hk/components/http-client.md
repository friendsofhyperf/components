# HTTP Client

適用於 Hyperf 的 HTTP 客戶端組件，移植自 Laravel，並由 Guzzle 提供底層支援。

## 安裝

```shell
composer require friendsofhyperf/http-client guzzlehttp/guzzle:^7.6
```

`guzzlehttp/guzzle` 是本套件建議安裝的依賴，傳送請求時必須可用。無需發佈設定檔。

## 傳送請求

使用 `Http` 門面傳送 `GET`、`HEAD`、`POST`、`PUT`、`PATCH` 和 `DELETE`
請求。請求內容預設以 JSON 格式傳送。

```php
use FriendsOfHyperf\Http\Client\Http;

$response = Http::get('https://example.com/users', [
    'page' => 1,
]);

$response = Http::post('https://example.com/users', [
    'name' => 'Taylor',
]);
```

在 HTTP 方法前以鏈式方法設定待傳送請求：

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

使用 `withUrlParameters()` 展開 URI 範本佔位符。使用 `asForm()`、`attach()`
或 `withBody()` 分別傳送表單、多部分或原始請求內容：

```php
$response = Http::withUrlParameters(['user' => 1])
    ->get('https://example.com/users/{user}');

$response = Http::asForm()->post('https://example.com/login', [
    'email' => 'taylor@example.com',
]);

$response = Http::attach('avatar', fopen('/path/to/avatar.jpg', 'r'), 'avatar.jpg')
    ->post('https://example.com/users/1/avatar');
```

預設連線逾時為 10 秒，請求總逾時為 30 秒。可使用 `withOptions()` 傳入其他
Guzzle 請求選項。只應在確實需要停用 TLS 憑證驗證時使用 `withoutVerifying()`。

## 重試

`retry()` 的第一個參數可以是總嘗試次數，也可以是以毫秒為單位的退避延遲陣列。
第二個參數可以是固定延遲或閉包。可選的第三個參數決定是否重試失敗回應或連線例外。

```php
use FriendsOfHyperf\Http\Client\PendingRequest;
use Throwable;

$response = Http::retry(
    [100, 200],
    0,
    fn (Throwable $exception, PendingRequest $request) => true,
)->get('https://example.com');
```

預設情況下，重試耗盡後會拋出例外。將第四個參數設為 `false` 可回傳最終失敗回應。

## 回應與例外

請求回傳 `FriendsOfHyperf\Http\Client\Response`。常用回應方法包括：

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

HTTP 4xx 和 5xx 回應預設不會拋出例外。在待傳送請求或回應上呼叫 `throw()` 可拋出
`RequestException`。連線失敗會拋出 `ConnectionException`。

```php
use FriendsOfHyperf\Http\Client\RequestException;

try {
    $response = Http::get('https://example.com/users/1')->throw();
} catch (RequestException $exception) {
    $response = $exception->response;
}
```

## 並行請求

使用 `pool()` 並行傳送請求。使用 `as()` 為回應指定鍵名。

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

`fake()` 會攔截符合的請求，並記錄請求以供斷言。呼叫 `preventStrayRequests()`
可拒絕沒有符合假回應的請求。

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

重複請求需要回傳不同回應時，可使用回應序列。序列耗盡後預設拋出
`OutOfBoundsException`，除非設定了 `whenEmpty()` 或 `dontFailWhenEmpty()`。

```php
Http::fakeSequence('example.com/*')
    ->push(['result' => 'first'])
    ->pushStatus(404);
```

其他可用斷言包括 `assertNotSent()`、`assertNothingSent()`、`assertSentInOrder()`
和 `assertSequencesAreEmpty()`。

## 中介軟件與事件

使用 `withMiddleware()`、`withRequestMiddleware()` 或 `withResponseMiddleware()`
加入單次請求中介軟件。`Factory` 還提供 `globalMiddleware()`、
`globalRequestMiddleware()` 和 `globalResponseMiddleware()`。

使用 PSR 事件分派器建立 `Factory` 時，組件會分派 `RequestSending`、
`ResponseReceived` 和 `ConnectionFailed` 事件。

## Laravel 相容性

本組件 API 基於 Laravel HTTP 客戶端，但實際可用 API 和行為以本組件源碼為準。
可參閱 [Laravel HTTP Client 文件](https://laravel.com/docs/9.x/http-client)
了解更多背景，並在使用前對照本組件確認 API。
