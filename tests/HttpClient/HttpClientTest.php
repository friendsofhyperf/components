<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Http\Client\Factory;
use FriendsOfHyperf\Http\Client\Http;
use FriendsOfHyperf\Http\Client\Request;
use FriendsOfHyperf\Http\Client\RequestException;
use FriendsOfHyperf\Http\Client\Response;
use GuzzleHttp\TransferStats;
use Psr\Http\Message\ResponseInterface;

use function Hyperf\Collection\collect;

uses()->group('http-client');

beforeEach(function () {
    $this->factory = new Factory();
});

test('test buildClient', function () {
    expect(Http::buildClient())->toBeInstanceOf(\GuzzleHttp\Client::class);
});

test('test ok', function () {
    $this->factory->fake([
        'laravel.com/*' => Http::response('', 200),
    ]);
    $response = $this->factory->get('http://laravel.com/');
    $this->assertTrue($response->ok());
});

test('test failed', function () {
    $this->factory->fake([
        'laravel.com/*' => Http::response('Not Found', 404),
    ]);

    $response = $this->factory->get('http://laravel.com/test-missing-page');
    $this->assertTrue($response->clientError());

    try {
        $response->throw();
    } catch (Throwable $e) {
        $this->assertInstanceOf(RequestException::class, $e);
    }
});

test('test get', function () {
    $this->factory->fake([
        'laravel.com/*' => Http::response($json = json_encode(['foo' => 'bar']), 200),
    ]);

    /** @var Response $response */
    $response = $this->factory->get('http://laravel.com/get');

    $this->assertTrue($response->ok());
    $this->assertIsArray($response->json());
    $this->assertArrayHasKey('foo', $response->json());
});

test('test post', function () {
    $this->factory->fake([
        'laravel.com/*' => Http::response($json = json_encode(['foo' => 'bar']), 200),
    ]);

    $response = $this->factory->post('http://laravel.com/post', ['foo' => 'bar']);

    $this->assertTrue($response->ok());
    $this->assertIsArray($response->json());
    $this->assertArrayHasKey('foo', $response->json());
});

test('test canSendArrayableFormData', function () {
    $this->factory->fake();

    $this->factory->asForm()->post('http://foo.com/form', collect([
        'name' => 'Taylor',
        'title' => 'Laravel Developer',
    ]));

    $this->factory->assertSent(function (Request $request) {
        return $request->url() === 'http://foo.com/form'
               && $request->hasHeader('Content-Type', 'application/x-www-form-urlencoded')
               && $request['name'] === 'Taylor';
    });
});

test('test getWithArrayableQueryParam', function () {
    $this->factory->fake();

    $this->factory->get('http://foo.com/get', collect(['foo' => 'bar']));

    $this->factory->assertSent(function (Request $request) {
        return $request->url() === 'http://foo.com/get?foo=bar'
            && $request['foo'] === 'bar';
    });
});

test('test redirect', function () {
    $this->factory->fake([
        'laravel.com/*' => Http::response('', 300),
    ]);
    $response = $this->factory->get('http://laravel.com/status/300');
    $this->assertTrue($response->redirect());
});

test('test reason', function () {
    $this->factory->fake([
        'laravel.com/*' => Http::response('', 401),
    ]);
    $response = $this->factory->get('http://laravel.com/status/401');
    $this->assertEquals('Unauthorized', $response->reason());
});

test('test unauthorized', function () {
    $this->factory->fake([
        'laravel.com/*' => Http::response('', 401),
    ]);
    $response = $this->factory->get('http://laravel.com/status/401');
    $this->assertTrue($response->unauthorized());
});

test('test forbidden', function () {
    $this->factory->fake([
        'laravel.com/*' => Http::response('', 403),
    ]);
    $response = $this->factory->get('http://laravel.com/status/403');
    $this->assertTrue($response->forbidden());
});

test('test notFound', function () {
    $this->factory->fake([
        'laravel.com/*' => Http::response('', 404),
    ]);
    $response = $this->factory->get('http://laravel.com/status/404');
    $this->assertTrue($response->notFound());
});

test('test basicAuth', function () {
    $user = 'admin';
    $pass = 'secret';
    $url = 'http://example.com/basic-auth';

    $this->factory->fake(function (Request $request) {
        if ($request->header('Authorization')[0] == 'Basic YWRtaW46c2VjcmV0') {
            return Http::response('', 200);
        }

        return Http::response('', 401);
    });

    $response = $this->factory->withBasicAuth($user, $pass)->get($url);
    $this->assertTrue($response->ok());

    $response = $this->factory->withBasicAuth($user, '')->get($url);
    $this->assertFalse($response->ok());
});

test('test requestExceptionIsThrownWhenRetriesExhausted', function () {
    $this->factory->fake([
        '*' => $this->factory->response(['error'], 403),
    ]);

    $exception = null;

    try {
        $this->factory
            ->retry(2, 1000, null, true)
            ->get('http://foo.com/get');
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNotNull($exception);
    $this->assertInstanceOf(RequestException::class, $exception);

    $this->factory->assertSentCount(2);
});

test('test requestExceptionIsNotThrownWhenDisabledAndRetriesExhausted', function () {
    $this->factory->fake([
        '*' => $this->factory->response(['error'], 403),
    ]);

    $response = $this->factory
        ->retry(2, 1000, null, false)
        ->get('http://foo.com/get');

    $this->assertTrue($response->failed());

    $this->factory->assertSentCount(2);
});

test('test sinkToFile', function () {
    $this->factory->fakeSequence()->push('abc123');

    $destination = '/tmp/sunk.txt';

    if (file_exists($destination)) {
        unlink($destination);
    }

    $this->factory->withOptions(['sink' => $destination])->get('https://example.com');

    $this->assertFileExists($destination);
    $this->assertSame('abc123', file_get_contents($destination));

    unlink($destination);
});

test('test onHeaders', function () {
    $this->factory
        ->onHeaders(function (ResponseInterface $response) {
            $this->assertGreaterThan(0, $response->getHeaderLine('Content-Length'));
        })
        ->accept('image/jpeg')
        ->get('http://httpbin.org/image');
});

test('test progress', function () {
    $this->factory
        ->progress(function ($downloadTotal, $downloaded, $uploadTotal, $uploaded) {
            $this->assertGreaterThanOrEqual(0, $downloadTotal);
            $this->assertGreaterThanOrEqual(0, $downloaded);
        })
        ->accept('image/jpeg')
        ->get('http://httpbin.org/image');
});

test('test requestExceptionIsThrownIfTheThrowIfClosureOnThePendingRequestReturnsTrue', function () {
    $this->factory->fake([
        'laravel.com/*' => Http::response('', 403),
    ]);

    $exception = null;

    $hitThrowCallback = false;

    try {
        $this->factory->throwIf(function ($response) {
            $this->assertInstanceOf(Response::class, $response);
            $this->assertSame(403, $response->status());

            return true;
        }, function ($response, $e) use (&$hitThrowCallback) {
            $this->assertInstanceOf(Response::class, $response);
            $this->assertSame(403, $response->status());

            $this->assertInstanceOf(RequestException::class, $e);
            $hitThrowCallback = true;
        })
            ->get('http://laravel.com/status/403');
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNotNull($exception);
    $this->assertInstanceOf(RequestException::class, $exception);
    $this->assertTrue($hitThrowCallback);
});

test('test requestExceptionIsNotThrownIfTheThrowIfClosureOnThePendingRequestReturnsFalse', function () {
    $this->factory->fake([
        'laravel.com/*' => Http::response('', 403),
    ]);
    $hitThrowCallback = false;

    $response = $this->factory->throwIf(function ($response) {
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(403, $response->status());

        return false;
    }, function ($response, $e) use (&$hitThrowCallback) {
        $hitThrowCallback = true;
    })
        ->get('http://laravel.com/status/403');

    $this->assertSame(403, $response->status());
    $this->assertFalse($hitThrowCallback);
});

test('test requestExceptionIsThrownIfStatusCodeIsSatisfied', function () {
    $this->factory->fake([
        '*' => $this->factory::response('', 400),
    ]);

    $exception = null;

    try {
        $this->factory->get('http://foo.com/api')->throwIfStatus(400);
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNotNull($exception);
    $this->assertInstanceOf(RequestException::class, $exception);
});
test('test requestExceptionIsThrownIfStatusCodeIsSatisfiedWithClosure', function () {
    $this->factory->fake([
        '*' => $this->factory::response('', 400),
    ]);

    $exception = null;

    try {
        $this->factory->get('http://foo.com/api')->throwIfStatus(fn ($status) => $status === 400);
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNotNull($exception);
    $this->assertInstanceOf(RequestException::class, $exception);
});

test('test requestExceptionIsNotThrownIfStatusCodeIsNotSatisfied', function () {
    $this->factory->fake([
        '*' => $this->factory::response('', 400),
    ]);

    $exception = null;

    try {
        $this->factory->get('http://foo.com/api')->throwIfStatus(500);
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNull($exception);
});

test('test requestExceptionIsThrownUnlessStatusCodeIsSatisfied', function () {
    $this->factory->fake([
        'http://foo.com/api/400' => $this->factory::response('', 400),
        'http://foo.com/api/408' => $this->factory::response('', 408),
        'http://foo.com/api/500' => $this->factory::response('', 500),
    ]);

    $exception = null;

    try {
        $this->factory->get('http://foo.com/api/400')->throwUnlessStatus(500);
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNotNull($exception);
    $this->assertInstanceOf(RequestException::class, $exception);

    $exception = null;

    $this->factory->fake([
        'http://foo.com/api/400' => $this->factory::response('', 400),
        'http://foo.com/api/408' => $this->factory::response('', 408),
        'http://foo.com/api/500' => $this->factory::response('', 500),
    ]);

    $exception = null;

    try {
        $this->factory->get('http://foo.com/api/400')->throwUnlessStatus(fn ($status) => $status === 500);
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNotNull($exception);
    $this->assertInstanceOf(RequestException::class, $exception);

    $exception = null;

    try {
        $this->factory->get('http://foo.com/api/408')->throwUnlessStatus(500);
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNotNull($exception);
    $this->assertInstanceOf(RequestException::class, $exception);

    $exception = null;

    try {
        $this->factory->get('http://foo.com/api/500')->throwUnlessStatus(500);
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNull($exception);
});
test('test requestExceptionIsThrownIfIsClientError', function () {
    $this->factory->fake([
        'http://foo.com/api/400' => $this->factory::response('', 400),
        'http://foo.com/api/408' => $this->factory::response('', 408),
        'http://foo.com/api/500' => $this->factory::response('', 500),
        'http://foo.com/api/504' => $this->factory::response('', 504),
    ]);

    $exception = null;

    try {
        $this->factory->get('http://foo.com/api/400')->throwIfClientError();
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNotNull($exception);
    $this->assertInstanceOf(RequestException::class, $exception);

    $exception = null;

    try {
        $this->factory->get('http://foo.com/api/408')->throwIfClientError();
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNotNull($exception);
    $this->assertInstanceOf(RequestException::class, $exception);

    $exception = null;

    try {
        $this->factory->get('http://foo.com/api/500')->throwIfClientError();
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNull($exception);

    $exception = null;

    try {
        $this->factory->get('http://foo.com/api/504')->throwIfClientError();
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNull($exception);
});
test('test requestExceptionIsThrownIfIsServerError', function () {
    $this->factory->fake([
        'http://foo.com/api/400' => $this->factory::response('', 400),
        'http://foo.com/api/408' => $this->factory::response('', 408),
        'http://foo.com/api/500' => $this->factory::response('', 500),
        'http://foo.com/api/504' => $this->factory::response('', 504),
    ]);

    $exception = null;

    try {
        $this->factory->get('http://foo.com/api/400')->throwIfServerError();
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNull($exception);

    $exception = null;

    try {
        $this->factory->get('http://foo.com/api/408')->throwIfServerError();
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNull($exception);

    $exception = null;

    try {
        $this->factory->get('http://foo.com/api/500')->throwIfServerError();
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNotNull($exception);
    $this->assertInstanceOf(RequestException::class, $exception);

    $exception = null;

    try {
        $this->factory->get('http://foo.com/api/504')->throwIfServerError();
    } catch (RequestException $e) {
        $exception = $e;
    }

    $this->assertNotNull($exception);
    $this->assertInstanceOf(RequestException::class, $exception);
});

test('test canSubstituteUrlParams', function (): void {
    $this->factory->fake();

    $this->factory->withUrlParameters([
        'endpoint' => 'https://laravel.com',
        'page' => 'docs',
        'version' => '9.x',
        'thing' => 'validation',
    ])->get('{+endpoint}/{page}/{version}/{thing}');

    $this->factory->assertSent(function (FriendsOfHyperf\Http\Client\Request $request) {
        return $request->url() === 'https://laravel.com/docs/9.x/validation';
    });
});

test('test theTransferStatsAreCustomizable', function () {
    $onStatsFunctionCalled = false;

    $stats = $this->factory
        ->withOptions([
            'on_stats' => function (TransferStats $stats) use (&$onStatsFunctionCalled) {
                $onStatsFunctionCalled = true;
            },
        ])
        ->get('https://example.com')
        ->handlerStats();

    $this->assertIsArray($stats);
    $this->assertNotEmpty($stats);
    $this->assertTrue($onStatsFunctionCalled);
});

test('test theTransferStatsAreCustomizableOnFake', function () {
    $onStatsFunctionCalled = false;

    $this->factory
        ->fake()
        ->withOptions([
            'on_stats' => function (TransferStats $stats) use (&$onStatsFunctionCalled) {
                $onStatsFunctionCalled = true;
            },
        ])
        ->get('https://foo.bar')
        ->handlerStats();

    $this->assertTrue($onStatsFunctionCalled);
});

test('test requestsWillBeWaitingSleepMillisecondsReceivedBeforeRetry', function () {
    $startTime = microtime(true);

    $this->factory->fake([
        '*' => $this->factory->sequence()
            ->push(['error'], 500)
            ->push(['error'], 500)
            ->push(['ok'], 200),
    ]);

    $this->factory
        ->retry(3, function ($attempt, $exception) {
            $this->assertInstanceOf(RequestException::class, $exception);

            return $attempt * 100;
        }, null, true)
        ->get('http://foo.com/get');

    $this->factory->assertSentCount(3);

    // Make sure was waited 300ms for the first two attempts
    $this->assertEqualsWithDelta(0.3, microtime(true) - $startTime, 0.03);
});
