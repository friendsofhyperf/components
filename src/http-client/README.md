# HTTP Client

An HTTP client component for Hyperf, ported from Laravel and backed by Guzzle.

## Installation

```shell
composer require friendsofhyperf/http-client guzzlehttp/guzzle:^7.6
```

`guzzlehttp/guzzle` is a suggested dependency of this package and is required to send
requests. No configuration file needs to be published.

## Sending Requests

Use the `Http` facade to send `GET`, `HEAD`, `POST`, `PUT`, `PATCH`, and `DELETE`
requests. Request bodies are sent as JSON by default.

```php
use FriendsOfHyperf\Http\Client\Http;

$response = Http::get('https://example.com/users', [
    'page' => 1,
]);

$response = Http::post('https://example.com/users', [
    'name' => 'Taylor',
]);
```

Configure a pending request by chaining methods before the HTTP verb:

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

Use `withUrlParameters()` to expand URI template placeholders. Use `asForm()`,
`attach()`, or `withBody()` for form, multipart, or raw request bodies:

```php
$response = Http::withUrlParameters(['user' => 1])
    ->get('https://example.com/users/{user}');

$response = Http::asForm()->post('https://example.com/login', [
    'email' => 'taylor@example.com',
]);

$response = Http::attach('avatar', fopen('/path/to/avatar.jpg', 'r'), 'avatar.jpg')
    ->post('https://example.com/users/1/avatar');
```

The default connect timeout is 10 seconds and the default request timeout is 30
seconds. Other Guzzle request options may be supplied with `withOptions()`. Use
`withoutVerifying()` only when intentionally disabling TLS certificate verification.

## Retries

`retry()` accepts either the total number of attempts or an array of backoff delays in
milliseconds. Its second argument may be a fixed delay or a closure. The optional
third argument decides whether a failed response or connection exception should be
retried.

```php
use FriendsOfHyperf\Http\Client\PendingRequest;
use Throwable;

$response = Http::retry(
    [100, 200],
    0,
    fn (Throwable $exception, PendingRequest $request) => true,
)->get('https://example.com');
```

By default, exhausted retries throw an exception. Pass `false` as the fourth argument
to return the final failed response instead.

## Responses and Errors

Requests return `FriendsOfHyperf\Http\Client\Response`. Common response methods
include:

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

HTTP 4xx and 5xx responses do not throw by default. Call `throw()` on the pending
request or response to throw `RequestException`. Connection failures throw
`ConnectionException`.

```php
use FriendsOfHyperf\Http\Client\RequestException;

try {
    $response = Http::get('https://example.com/users/1')->throw();
} catch (RequestException $exception) {
    $response = $exception->response;
}
```

## Concurrent Requests

Use `pool()` to send requests concurrently. Use `as()` to assign response keys.

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

## Testing

`fake()` intercepts matching requests and records them for assertions. Call
`preventStrayRequests()` to reject requests without a matching fake.

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

Use a response sequence when repeated requests should receive different responses.
An exhausted sequence throws `OutOfBoundsException` unless `whenEmpty()` or
`dontFailWhenEmpty()` is configured.

```php
Http::fakeSequence('example.com/*')
    ->push(['result' => 'first'])
    ->pushStatus(404);
```

Other available assertions include `assertNotSent()`, `assertNothingSent()`,
`assertSentInOrder()`, and `assertSequencesAreEmpty()`.

## Middleware and Events

Add per-request middleware with `withMiddleware()`, `withRequestMiddleware()`, or
`withResponseMiddleware()`. `Factory` also provides `globalMiddleware()`,
`globalRequestMiddleware()`, and `globalResponseMiddleware()`.

When the `Factory` is created with a PSR event dispatcher, it dispatches
`RequestSending`, `ResponseReceived`, and `ConnectionFailed` events.

## Laravel Compatibility

The API is based on Laravel's HTTP client, but the available API and behavior are
defined by this component's source code. Consult the
[Laravel HTTP Client documentation](https://laravel.com/docs/9.x/http-client) for
additional background, and verify APIs against this component before using them.
