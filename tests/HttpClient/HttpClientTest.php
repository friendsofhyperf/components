<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Tests\HttpClient;

use FriendsOfHyperf\Http\Client\Http;
use FriendsOfHyperf\Http\Client\RequestException;
use FriendsOfHyperf\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 * @coversNothing
 */
class HttpClientTest extends TestCase
{
    public function testBuildClient()
    {
        $client = Http::buildClient();

        $this->assertInstanceOf(\GuzzleHttp\Client::class, $client);
    }

    public function testOk()
    {
        $response = Http::get('http://www.baidu.com');
        $this->assertTrue($response->ok());
    }

    public function testFailed()
    {
        $response = Http::get('http://laravel.com/test-missing-page');
        $this->assertTrue($response->clientError());

        $this->expectException(\FriendsOfHyperf\Http\Client\RequestException::class);
        $response->throw();
    }

    public function testGet()
    {
        $response = Http::get('http://httpbin.org/get');
        $this->assertTrue($response->ok());
        $this->assertIsArray($response->json());
        $this->assertArrayHasKey('args', $response->json());
    }

    public function testPost()
    {
        $response = Http::post('http://httpbin.org/post', ['foo' => 'bar']);
        $this->assertTrue($response->ok());
        $this->assertIsArray($response->json());
        $this->assertArrayHasKey('json', $response->json());
        $this->assertArrayHasKey('foo', $response->json()['json']);
        $this->assertEquals('bar', $response->json()['json']['foo']);
    }

    // public function testCanSendArrayableFormData()
    // {
    //     $response = Http::asForm()->post('http://httpbin.org/post', collect(['foo' => 'bar']));
    //     $this->assertTrue($response->ok());
    //     $this->assertIsArray($response->json());
    //     $this->assertArrayHasKey('form', $response->json());
    //     $this->assertArrayHasKey('foo', $response->json()['form']);
    //     $this->assertEquals('bar', $response->json()['form']['foo']);
    // }

    // public function testGetWithArrayableQueryParam()
    // {
    //     $response = Http::get('http://httpbin.org/get', collect(['foo' => 'bar']));
    //     $this->assertTrue($response->ok());
    //     $this->assertIsArray($response->json());
    //     $this->assertArrayHasKey('args', $response->json());
    //     $this->assertArrayHasKey('foo', $response->json()['args']);
    //     $this->assertEquals('bar', $response->json()['args']['foo']);
    // }

    public function testRedirect()
    {
        $response = Http::get('http://httpbin.org/status/300');
        $this->assertTrue($response->redirect());
    }

    public function testReason()
    {
        $response = Http::get('http://httpbin.org/status/401');
        $this->assertEquals('UNAUTHORIZED', $response->reason());
    }

    public function testUnauthorized()
    {
        $response = Http::get('http://httpbin.org/status/401');
        $this->assertTrue($response->unauthorized());
    }

    public function testForbidden()
    {
        $response = Http::get('http://httpbin.org/status/403');
        $this->assertTrue($response->forbidden());
    }

    public function testBasicAuth()
    {
        $user = 'admin';
        $pass = 'secret';
        $url = sprintf('http://httpbin.org/basic-auth/%s/%s', $user, $pass);

        $response = Http::withBasicAuth($user, $pass)->get($url);
        $this->assertTrue($response->ok());

        $response = Http::withBasicAuth($user, '')->get($url);
        $this->assertFalse($response->ok());
    }

    public function testRequestExceptionIsThrownWhenRetriesExhausted()
    {
        $this->expectException(RequestException::class);

        Http::retry(2, 1000, null, true)
            ->get('http://foo.com/get');
    }

    // public function testRequestExceptionIsNotThrownWhenDisabledAndRetriesExhausted()
    // {
    //     $response = Http::retry(2, 1000, null, false)
    //         ->get('http://foo.com/get');

    //     $this->assertTrue($response->failed());
    // }

    public function testSink()
    {
        try {
            $sink = '/tmp/tmp.jpg';
            Http::sink($sink)
                ->accept('image/jpeg')
                ->get('http://httpbin.org/image');
            $this->assertFileExists($sink);
        } finally {
            @unlink($sink);
        }
    }

    public function testOnHeaders()
    {
        Http::onHeaders(function (ResponseInterface $response) {
            $this->assertGreaterThan(0, $response->getHeaderLine('Content-Length'));
        })
            ->accept('image/jpeg')
            ->get('http://httpbin.org/image');
    }

    public function testProgress()
    {
        Http::progress(function ($downloadTotal, $downloaded, $uploadTotal, $uploaded) {
            $this->assertGreaterThanOrEqual(0, $downloadTotal);
            $this->assertGreaterThanOrEqual(0, $downloaded);
        })
            ->accept('image/jpeg')
            ->get('http://httpbin.org/image');
    }
}
