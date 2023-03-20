<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Http\Client\Http;
use FriendsOfHyperf\Http\Client\RequestException;
use FriendsOfHyperf\Http\Client\Response;
use Psr\Http\Message\ResponseInterface;

test('test BuildClient', function () {
    $client = Http::buildClient();

    $this->assertInstanceOf(\GuzzleHttp\Client::class, $client);
});

test('test Ok', function () {
    $response = Http::get('http://www.baidu.com');
    $this->assertTrue($response->ok());
});

test('test Failed', function () {
    $response = Http::get('http://laravel.com/test-missing-page');
    $this->assertTrue($response->clientError());

    $this->expectException(\FriendsOfHyperf\Http\Client\RequestException::class);
    $response->throw();
});

test('test Get', function () {
    $response = Http::get('http://httpbin.org/get');
    $this->assertTrue($response->ok());
    $this->assertIsArray($response->json());
    $this->assertArrayHasKey('args', $response->json());
});

test('test Post', function () {
    $response = Http::post('http://httpbin.org/post', ['foo' => 'bar']);
    $this->assertTrue($response->ok());
    $this->assertIsArray($response->json());
    $this->assertArrayHasKey('json', $response->json());
    $this->assertArrayHasKey('foo', $response->json()['json']);
    $this->assertEquals('bar', $response->json()['json']['foo']);
});

// test('test CanSendArrayableFormData', function()
// {
    //     $response = Http::asForm()->post('http://httpbin.org/post', collect(['foo' => 'bar']));
    //     $this->assertTrue($response->ok());
    //     $this->assertIsArray($response->json());
    //     $this->assertArrayHasKey('form', $response->json());
    //     $this->assertArrayHasKey('foo', $response->json()['form']);
    //     $this->assertEquals('bar', $response->json()['form']['foo']);
// });

// test('test GetWithArrayableQueryParam', function()
// {
    //     $response = Http::get('http://httpbin.org/get', collect(['foo' => 'bar']));
    //     $this->assertTrue($response->ok());
    //     $this->assertIsArray($response->json());
    //     $this->assertArrayHasKey('args', $response->json());
    //     $this->assertArrayHasKey('foo', $response->json()['args']);
    //     $this->assertEquals('bar', $response->json()['args']['foo']);
// });

test('test Redirect', function () {
    $response = Http::get('http://httpbin.org/status/300');
    $this->assertTrue($response->redirect());
});

test('test Reason', function () {
    $response = Http::get('http://httpbin.org/status/401');
    $this->assertEquals('UNAUTHORIZED', $response->reason());
});

test('test Unauthorized', function () {
    $response = Http::get('http://httpbin.org/status/401');
    $this->assertTrue($response->unauthorized());
});

test('test Forbidden', function () {
    $response = Http::get('http://httpbin.org/status/403');
    $this->assertTrue($response->forbidden());
});

test('test NotFound', function () {
    $response = Http::get('http://httpbin.org/status/404');
    $this->assertTrue($response->notFound());
});

test('test BasicAuth', function () {
    $user = 'admin';
    $pass = 'secret';
    $url = sprintf('http://httpbin.org/basic-auth/%s/%s', $user, $pass);

    $response = Http::withBasicAuth($user, $pass)->get($url);
    $this->assertTrue($response->ok());

    $response = Http::withBasicAuth($user, '')->get($url);
    $this->assertFalse($response->ok());
});

test('test RequestExceptionIsThrownWhenRetriesExhausted', function () {
    $this->expectException(RequestException::class);

    Http::retry(2, 1000, null, true)
        ->get('http://foo.com/get');
});

// test('test RequestExceptionIsNotThrownWhenDisabledAndRetriesExhausted', function()
// {
    //     $response = Http::retry(2, 1000, null, false)
    //         ->get('http://foo.com/get');

    //     $this->assertTrue($response->failed());
// });

test('test Sink', function () {
    try {
        $sink = '/tmp/tmp.jpg';
        Http::sink($sink)
            ->accept('image/jpeg')
            ->get('http://httpbin.org/image');
        $this->assertFileExists($sink);
    } finally {
        @unlink($sink);
    }
});

test('test OnHeaders', function () {
    Http::onHeaders(function (ResponseInterface $response) {
        $this->assertGreaterThan(0, $response->getHeaderLine('Content-Length'));
    })
        ->accept('image/jpeg')
        ->get('http://httpbin.org/image');
});

test('test Progress', function () {
    Http::progress(function ($downloadTotal, $downloaded, $uploadTotal, $uploaded) {
        $this->assertGreaterThanOrEqual(0, $downloadTotal);
        $this->assertGreaterThanOrEqual(0, $downloaded);
    })
        ->accept('image/jpeg')
        ->get('http://httpbin.org/image');
});

test('test RequestExceptionIsThrownIfTheThrowIfClosureOnThePendingRequestReturnsTrue', function () {
    $exception = null;

    $hitThrowCallback = false;

    try {
        Http::throwIf(function ($response) {
            $this->assertInstanceOf(Response::class, $response);
            $this->assertSame(403, $response->status());

            return true;
        }, function ($response, $e) use (&$hitThrowCallback) {
            $this->assertInstanceOf(Response::class, $response);
            $this->assertSame(403, $response->status());

            $this->assertInstanceOf(RequestException::class, $e);
            $hitThrowCallback = true;
        })
            ->get('http://httpbin.org/status/403');
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNotNull($exception);
    $this->assertInstanceOf(RequestException::class, $exception);
    $this->assertTrue($hitThrowCallback);
});

test('test RequestExceptionIsNotThrownIfTheThrowIfClosureOnThePendingRequestReturnsFalse', function () {
    $hitThrowCallback = false;

    $response = Http::throwIf(function ($response) {
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(403, $response->status());

        return false;
    }, function ($response, $e) use (&$hitThrowCallback) {
        $hitThrowCallback = true;
    })
        ->get('http://httpbin.org/status/403');

    $this->assertSame(403, $response->status());
    $this->assertFalse($hitThrowCallback);
});

test('test RequestExceptionIsThrownIfStatusCodeIsSatisfied', function () {
    $exception = null;

    try {
        Http::get('http://httpbin.org/status/400')->throwIfStatus(400);
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNotNull($exception);
    $this->assertInstanceOf(RequestException::class, $exception);
});

test('test RequestExceptionIsThrownIfStatusCodeIsSatisfiedWithClosure', function () {
    $exception = null;

    try {
        Http::get('http://httpbin.org/status/400')->throwIfStatus(fn ($status) => $status === 400);
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNotNull($exception);
    $this->assertInstanceOf(RequestException::class, $exception);
});

test('test RequestExceptionIsNotThrownIfStatusCodeIsNotSatisfied', function () {
    $exception = null;

    try {
        Http:// get('http://httpbin.org/status/500')->throwIfStatus(500);
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNull($exception);
});

test('test RequestExceptionIsThrownUnlessStatusCodeIsSatisfied', function () {
    $exception = null;

    try {
        Http::get('http://httpbin.org/status/400')->throwUnlessStatus(500);
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNotNull($exception);
    $this->assertInstanceOf(RequestException::class, $exception);

    $exception = null;

    try {
        Http::get('http://httpbin.org/status/400')->throwUnlessStatus(fn ($status) => $status === 500);
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNotNull($exception);
    $this->assertInstanceOf(RequestException::class, $exception);

    $exception = null;

    try {
        Http::get('http://httpbin.org/status/408')->throwUnlessStatus(500);
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNotNull($exception);
    $this->assertInstanceOf(RequestException::class, $exception);

    $exception = null;

    try {
        Http::get('http://httpbin.org/status/500')->throwUnlessStatus(500);
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNull($exception);
});

test('test RequestExceptionIsThrownIfIsClientError', function () {
    $exception = null;

    try {
        Http::get('http://httpbin.org/status/400')->throwIfClientError();
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNotNull($exception);
    $this->assertInstanceOf(RequestException::class, $exception);

    $exception = null;

    try {
        Http::get('http://httpbin.org/status/408')->throwIfClientError();
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNotNull($exception);
    $this->assertInstanceOf(RequestException::class, $exception);

    $exception = null;

    try {
        Http::get('http://httpbin.org/status/500')->throwIfClientError();
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNull($exception);

    $exception = null;

    try {
        Http::get('http://httpbin.org/status/504')->throwIfClientError();
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNull($exception);
});

test('test RequestExceptionIsThrownIfIsServerError', function () {
    $exception = null;

    try {
        Http::get('http://httpbin.org/status/400')->throwIfServerError();
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNull($exception);

    $exception = null;

    try {
        Http::get('http://httpbin.org/status/408')->throwIfServerError();
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNull($exception);

    $exception = null;

    try {
        Http::get('http://httpbin.org/status/500')->throwIfServerError();
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNotNull($exception);
    $this->assertInstanceOf(RequestException::class, $exception);

    $exception = null;

    try {
        Http::get('http://httpbin.org/status/504')->throwIfServerError();
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNotNull($exception);
    $this->assertInstanceOf(RequestException::class, $exception);
});

test('test ItCanSubstituteUrlParams', function (): void {
    $response = Http::withUrlParameters([
        'endpoint' => 'https://laravel.com',
        'page' => 'docs',
        'version' => '9.x',
        'thing' => 'validation',
    ])->get('{+endpoint}/{page}/{version}/{thing}');

    $this->assertEquals('https://laravel.com/docs/9.x/validation', (string) $response->transferStats->getEffectiveUri());
});
