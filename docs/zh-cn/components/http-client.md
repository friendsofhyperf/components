# HTTP Client

适用于 Hyperf 的 HTTP 客户端组件，移植自 Laravel，并由 Guzzle 提供底层支持。

## 安装

```shell
composer require friendsofhyperf/http-client guzzlehttp/guzzle:^7.6
```

`guzzlehttp/guzzle` 是本包建议安装的依赖，发送请求时必须可用。无需发布配置文件。

## 发送请求

使用 `Http` 门面发送 `GET`、`HEAD`、`POST`、`PUT`、`PATCH` 和 `DELETE`
请求。请求体默认以 JSON 格式发送。

```php
use FriendsOfHyperf\Http\Client\Http;

$response = Http::get('https://example.com/users', [
    'page' => 1,
]);

$response = Http::post('https://example.com/users', [
    'name' => 'Taylor',
]);
```

在 HTTP 方法前链式调用方法以配置待发送请求：

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

使用 `withUrlParameters()` 展开 URI 模板占位符。使用 `asForm()`、`attach()`
或 `withBody()` 分别发送表单、多部分或原始请求体：

```php
$response = Http::withUrlParameters(['user' => 1])
    ->get('https://example.com/users/{user}');

$response = Http::asForm()->post('https://example.com/login', [
    'email' => 'taylor@example.com',
]);

$response = Http::attach('avatar', fopen('/path/to/avatar.jpg', 'r'), 'avatar.jpg')
    ->post('https://example.com/users/1/avatar');
```

默认连接超时为 10 秒，请求总超时为 30 秒。可使用 `withOptions()` 传入其他
Guzzle 请求选项。仅在确实需要禁用 TLS 证书验证时使用 `withoutVerifying()`。

## 重试

`retry()` 的第一个参数可以是总尝试次数，也可以是以毫秒为单位的退避延迟数组。
第二个参数可以是固定延迟或闭包。可选的第三个参数决定是否重试失败响应或连接异常。

```php
use FriendsOfHyperf\Http\Client\PendingRequest;
use Throwable;

$response = Http::retry(
    [100, 200],
    0,
    fn (Throwable $exception, PendingRequest $request) => true,
)->get('https://example.com');
```

默认情况下，重试耗尽后会抛出异常。将第四个参数设为 `false` 可返回最终失败响应。

## 响应与异常

请求返回 `FriendsOfHyperf\Http\Client\Response`。常用响应方法包括：

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

HTTP 4xx 和 5xx 响应默认不会抛出异常。在待发送请求或响应上调用 `throw()` 可抛出
`RequestException`。连接失败会抛出 `ConnectionException`。

```php
use FriendsOfHyperf\Http\Client\RequestException;

try {
    $response = Http::get('https://example.com/users/1')->throw();
} catch (RequestException $exception) {
    $response = $exception->response;
}
```

## 并发请求

使用 `pool()` 并发发送请求。使用 `as()` 为响应指定键名。

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

## 测试

`fake()` 会拦截匹配的请求，并记录请求以供断言。调用 `preventStrayRequests()`
可拒绝没有匹配假响应的请求。

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

重复请求需要返回不同响应时，可使用响应序列。序列耗尽后默认抛出
`OutOfBoundsException`，除非配置了 `whenEmpty()` 或 `dontFailWhenEmpty()`。

```php
Http::fakeSequence('example.com/*')
    ->push(['result' => 'first'])
    ->pushStatus(404);
```

其他可用断言包括 `assertNotSent()`、`assertNothingSent()`、`assertSentInOrder()`
和 `assertSequencesAreEmpty()`。

## 中间件与事件

使用 `withMiddleware()`、`withRequestMiddleware()` 或 `withResponseMiddleware()`
添加单次请求中间件。`Factory` 还提供 `globalMiddleware()`、
`globalRequestMiddleware()` 和 `globalResponseMiddleware()`。

使用 PSR 事件分发器创建 `Factory` 时，组件会分发 `RequestSending`、
`ResponseReceived` 和 `ConnectionFailed` 事件。

## Laravel 兼容性

本组件 API 基于 Laravel HTTP 客户端，但实际可用 API 和行为以本组件源码为准。
可参阅 [Laravel HTTP Client 文档](https://laravel.com/docs/9.x/http-client)
了解更多背景，并在使用前对照本组件确认 API。
