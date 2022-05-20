<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Http\Client;

use Exception;
use FriendsOfHyperf\Http\Client\Events\ConnectionFailed;
use FriendsOfHyperf\Http\Client\Events\RequestSending;
use FriendsOfHyperf\Http\Client\Events\ResponseReceived;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\HandlerStack;
use Hyperf\Guzzle\CoroutineHandler;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Collection;
use Hyperf\Utils\Coroutine;
use Hyperf\Utils\Str;
use Hyperf\Utils\Traits\Conditionable;
use Hyperf\Utils\Traits\Macroable;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\VarDumper\VarDumper;

class PendingRequest
{
    use Conditionable;
    use Macroable;

    /**
     * The factory instance.
     *
     * @var null|\Factory
     */
    protected $factory;

    /**
     * The Guzzle client instance.
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * The base URL for the request.
     *
     * @var string
     */
    protected $baseUrl = '';

    /**
     * The request body format.
     *
     * @var string
     */
    protected $bodyFormat;

    /**
     * The raw body for the request.
     *
     * @var string
     */
    protected $pendingBody;

    /**
     * The pending files for the request.
     *
     * @var array
     */
    protected $pendingFiles = [];

    /**
     * The request cookies.
     *
     * @var array
     */
    protected $cookies;

    /**
     * The transfer stats for the request.
     *
     * \GuzzleHttp\TransferStats
     */
    protected $transferStats;

    /**
     * The request options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * The number of times to try the request.
     *
     * @var int
     */
    protected $tries = 1;

    /**
     * The number of milliseconds to wait between retries.
     *
     * @var int
     */
    protected $retryDelay = 100;

    /**
     * Whether to throw an exception when all retries fail.
     *
     * @var bool
     */
    protected $retryThrow = true;

    /**
     * The callback that will determine if the request should be retried.
     *
     * @var null|callable
     */
    protected $retryWhenCallback;

    /**
     * The callbacks that should execute before the request is sent.
     *
     * @var Collection
     */
    protected $beforeSendingCallbacks;

    /**
     * The stub callables that will handle requests.
     *
     * @var null|Collection
     */
    protected $stubCallbacks;

    /**
     * The middleware callables added by users that will handle requests.
     *
     * @var Collection
     */
    protected $middleware;

    /**
     * Whether the requests should be asynchronous.
     *
     * @var bool
     */
    protected $async = false;

    /**
     * The pending request promise.
     *
     * @var \GuzzleHttp\Promise\PromiseInterface
     */
    protected $promise;

    /**
     * The sent request object, if a request has been made.
     *
     * @var null|\Request
     */
    protected $request;

    /**
     * The Guzzle request options that are mergable via array_merge_recursive.
     *
     * @var array
     */
    protected $mergableOptions = [
        'cookies',
        'form_params',
        'headers',
        'json',
        'multipart',
        'query',
    ];

    /**
     * Create a new HTTP Client instance.
     *
     * @param null|\Factory $factory
     */
    public function __construct(Factory $factory = null)
    {
        $this->factory = $factory;
        $this->middleware = new Collection();

        $this->asJson();

        $this->options = [
            'http_errors' => false,
        ];

        $this->beforeSendingCallbacks = collect([function (Request $request, array $options, PendingRequest $pendingRequest) {
            $pendingRequest->request = $request;
            $pendingRequest->cookies = $options['cookies'];

            $pendingRequest->dispatchRequestSendingEvent();
        }]);
    }

    /**
     * Set the base URL for the pending request.
     *
     * @return $this
     */
    public function baseUrl(string $url)
    {
        $this->baseUrl = $url;

        return $this;
    }

    /**
     * Attach a raw body to the request.
     *
     * @param string $content
     * @param string $contentType
     * @return $this
     */
    public function withBody($content, $contentType)
    {
        $this->bodyFormat('body');

        $this->pendingBody = $content;

        $this->contentType($contentType);

        return $this;
    }

    /**
     * Indicate the request contains JSON.
     *
     * @return $this
     */
    public function asJson()
    {
        return $this->bodyFormat('json')->contentType('application/json');
    }

    /**
     * Indicate the request contains form parameters.
     *
     * @return $this
     */
    public function asForm()
    {
        return $this->bodyFormat('form_params')->contentType('application/x-www-form-urlencoded');
    }

    /**
     * Attach a file to the request.
     *
     * @param array|string $name
     * @param resource|string $contents
     * @param null|string $filename
     * @return $this
     */
    public function attach($name, $contents = '', $filename = null, array $headers = [])
    {
        if (is_array($name)) {
            foreach ($name as $file) {
                $this->attach(...$file);
            }

            return $this;
        }

        $this->asMultipart();

        $this->pendingFiles[] = array_filter([
            'name' => $name,
            'contents' => $contents,
            'headers' => $headers,
            'filename' => $filename,
        ]);

        return $this;
    }

    /**
     * Indicate the request is a multi-part form request.
     *
     * @return $this
     */
    public function asMultipart()
    {
        return $this->bodyFormat('multipart');
    }

    /**
     * Specify the body format of the request.
     *
     * @return $this
     */
    public function bodyFormat(string $format)
    {
        return tap($this, function ($request) use ($format) {
            $this->bodyFormat = $format;
        });
    }

    /**
     * Specify the request's content type.
     *
     * @return $this
     */
    public function contentType(string $contentType)
    {
        return $this->withHeaders(['Content-Type' => $contentType]);
    }

    /**
     * Indicate that JSON should be returned by the server.
     *
     * @return $this
     */
    public function acceptJson()
    {
        return $this->accept('application/json');
    }

    /**
     * Indicate the type of content that should be returned by the server.
     *
     * @param string $contentType
     * @return $this
     */
    public function accept($contentType)
    {
        return $this->withHeaders(['Accept' => $contentType]);
    }

    /**
     * Add the given headers to the request.
     *
     * @return $this
     */
    public function withHeaders(array $headers)
    {
        return tap($this, function ($request) use ($headers) {
            return $this->options = array_merge_recursive($this->options, [
                'headers' => $headers,
            ]);
        });
    }

    /**
     * Specify the basic authentication username and password for the request.
     *
     * @return $this
     */
    public function withBasicAuth(string $username, string $password)
    {
        return tap($this, function ($request) use ($username, $password) {
            return $this->options['auth'] = [$username, $password];
        });
    }

    /**
     * Specify the digest authentication username and password for the request.
     *
     * @param string $username
     * @param string $password
     * @return $this
     */
    public function withDigestAuth($username, $password)
    {
        return tap($this, function ($request) use ($username, $password) {
            return $this->options['auth'] = [$username, $password, 'digest'];
        });
    }

    /**
     * Specify an authorization token for the request.
     *
     * @param string $token
     * @param string $type
     * @return $this
     */
    public function withToken($token, $type = 'Bearer')
    {
        return tap($this, function ($request) use ($token, $type) {
            return $this->options['headers']['Authorization'] = trim($type . ' ' . $token);
        });
    }

    /**
     * Specify the user agent for the request.
     *
     * @param string $userAgent
     * @return $this
     */
    public function withUserAgent($userAgent)
    {
        return tap($this, function ($request) use ($userAgent) {
            return $this->options['headers']['User-Agent'] = trim($userAgent);
        });
    }

    /**
     * Specify the cookies that should be included with the request.
     *
     * @return $this
     */
    public function withCookies(array $cookies, string $domain)
    {
        return tap($this, function ($request) use ($cookies, $domain) {
            return $this->options = array_merge_recursive($this->options, [
                'cookies' => CookieJar::fromArray($cookies, $domain),
            ]);
        });
    }

    /**
     * Indicate that redirects should not be followed.
     *
     * @return $this
     */
    public function withoutRedirecting()
    {
        return tap($this, function ($request) {
            return $this->options['allow_redirects'] = false;
        });
    }

    /**
     * Indicate that TLS certificates should not be verified.
     *
     * @return $this
     */
    public function withoutVerifying()
    {
        return tap($this, function ($request) {
            return $this->options['verify'] = false;
        });
    }

    /**
     * Specify the path where the body of the response should be stored.
     *
     * @param resource|string $to
     * @return $this
     */
    public function sink($to)
    {
        return tap($this, function ($request) use ($to) {
            return $this->options['sink'] = $to;
        });
    }

    /**
     * Specify the timeout (in seconds) for the request.
     *
     * @return $this
     */
    public function timeout(int $seconds)
    {
        return tap($this, function () use ($seconds) {
            $this->options['timeout'] = $seconds;
        });
    }

    /**
     * Specify the number of times the request should be attempted.
     *
     * @return $this
     */
    public function retry(int $times, int $sleep = 0, ?callable $when = null)
    {
        $this->tries = $times;
        $this->retryDelay = $sleep;
        $this->retryWhenCallback = $when;

        return $this;
    }

    /**
     * Replace the specified options on the request.
     *
     * @return $this
     */
    public function withOptions(array $options)
    {
        return tap($this, function ($request) use ($options) {
            return $this->options = array_replace_recursive(
                array_merge_recursive($this->options, Arr::only($options, $this->mergableOptions)),
                $options
            );
        });
    }

    /**
     * Add new middleware the client handler stack.
     *
     * @return $this
     */
    public function withMiddleware(callable $middleware)
    {
        $this->middleware->push($middleware);

        return $this;
    }

    /**
     * Add a new "before sending" callback to the request.
     *
     * @param callable $callback
     * @return $this
     */
    public function beforeSending($callback)
    {
        return tap($this, function () use ($callback) {
            $this->beforeSendingCallbacks[] = $callback;
        });
    }

    /**
     * Dump the request before sending.
     *
     * @return $this
     */
    public function dump()
    {
        $values = func_get_args();

        return $this->beforeSending(function (Request $request, array $options) use ($values) {
            foreach (array_merge($values, [$request, $options]) as $value) {
                VarDumper::dump($value);
            }
        });
    }

    /**
     * Dump the request before sending and end the script.
     *
     * @return $this
     */
    public function dd()
    {
        $values = func_get_args();

        return $this->beforeSending(function (Request $request, array $options) use ($values) {
            foreach (array_merge($values, [$request, $options]) as $value) {
                VarDumper::dump($value);
            }

            exit(1);
        });
    }

    /**
     * Issue a GET request to the given URL.
     *
     * @param null|array|string $query
     * @return Response
     */
    public function get(string $url, $query = null)
    {
        return $this->send('GET', $url, func_num_args() === 1 ? [] : [
            'query' => $query,
        ]);
    }

    /**
     * Issue a HEAD request to the given URL.
     *
     * @param null|array|string $query
     * @return Response
     */
    public function head(string $url, $query = null)
    {
        return $this->send('HEAD', $url, func_num_args() === 1 ? [] : [
            'query' => $query,
        ]);
    }

    /**
     * Issue a POST request to the given URL.
     *
     * @return Response
     */
    public function post(string $url, array $data = [])
    {
        return $this->send('POST', $url, [
            $this->bodyFormat => $data,
        ]);
    }

    /**
     * Issue a PATCH request to the given URL.
     *
     * @param string $url
     * @param array $data
     * @return Response
     */
    public function patch($url, $data = [])
    {
        return $this->send('PATCH', $url, [
            $this->bodyFormat => $data,
        ]);
    }

    /**
     * Issue a PUT request to the given URL.
     *
     * @param string $url
     * @param array $data
     * @return Response
     */
    public function put($url, $data = [])
    {
        return $this->send('PUT', $url, [
            $this->bodyFormat => $data,
        ]);
    }

    /**
     * Issue a DELETE request to the given URL.
     *
     * @param string $url
     * @param array $data
     * @return Response
     */
    public function delete($url, $data = [])
    {
        return $this->send('DELETE', $url, empty($data) ? [] : [
            $this->bodyFormat => $data,
        ]);
    }

    /**
     * Send a pool of asynchronous requests concurrently.
     *
     * @return array
     */
    public function pool(callable $callback)
    {
        $results = [];

        $requests = tap(new Pool($this->factory), $callback)->getRequests();

        foreach ($requests as $key => $item) {
            $results[$key] = $item instanceof static ? $item->getPromise()->wait() : $item->wait();
        }

        return $results;
    }

    /**
     * Send the request to the given URL.
     *
     * @throws \Exception
     * @return Response
     */
    public function send(string $method, string $url, array $options = [])
    {
        $url = ltrim(rtrim($this->baseUrl, '/') . '/' . ltrim($url, '/'), '/');

        if (isset($options[$this->bodyFormat])) {
            if ($this->bodyFormat === 'multipart') {
                $options[$this->bodyFormat] = $this->parseMultipartBodyFormat($options[$this->bodyFormat]);
            } elseif ($this->bodyFormat === 'body') {
                $options[$this->bodyFormat] = $this->pendingBody;
            }

            if (is_array($options[$this->bodyFormat])) {
                $options[$this->bodyFormat] = array_merge(
                    $options[$this->bodyFormat],
                    $this->pendingFiles
                );
            }
        } else {
            $options[$this->bodyFormat] = $this->pendingBody;
        }

        [$this->pendingBody, $this->pendingFiles] = [null, []];

        if ($this->async) {
            return $this->makePromise($method, $url, $options);
        }

        $shouldRetry = null;

        return retry($this->tries ?? 1, function ($attempt) use ($method, $url, $options, &$shouldRetry) {
            try {
                return tap(new Response($this->sendRequest($method, $url, $options)), function ($response) use ($attempt, &$shouldRetry) {
                    $this->populateResponse($response);

                    $this->dispatchResponseReceivedEvent($response);

                    if (! $response->successful()) {
                        try {
                            $shouldRetry = $this->retryWhenCallback ? call_user_func($this->retryWhenCallback, $response->toException(), $this) : true;
                        } catch (Exception $exception) {
                            $shouldRetry = false;

                            throw $exception;
                        }

                        if ($attempt < $this->tries && $shouldRetry) {
                            $response->throw();
                        }

                        if ($this->tries > 1 && $this->retryThrow) {
                            $response->throw();
                        }
                    }

                    $this->dispatchResponseReceivedEvent($response);
                });
            } catch (ConnectException $e) {
                $this->dispatchConnectionFailedEvent();

                throw new ConnectionException($e->getMessage(), 0, $e);
            }
        }, $this->retryDelay ?? 100, function ($exception) use (&$shouldRetry) {
            $result = $shouldRetry ?? ($this->retryWhenCallback ? call_user_func($this->retryWhenCallback, $exception, $this) : true);

            $shouldRetry = null;

            return $result;
        });
    }

    /**
     * Build the Guzzle client.
     *
     * @return \GuzzleHttp\Client
     */
    public function buildClient()
    {
        return $this->requestsReusableClient()
               ? $this->getReusableClient()
               : $this->createClient($this->buildHandlerStack());
    }

    /**
     * Create new Guzzle client.
     *
     * @param \GuzzleHttp\HandlerStack $handlerStack
     * @return \GuzzleHttp\Client
     */
    public function createClient($handlerStack)
    {
        return new Client([
            'handler' => $handlerStack,
            'cookies' => true,
        ]);
    }

    /**
     * Build the Guzzle client handler stack.
     *
     * @return \GuzzleHttp\HandlerStack
     */
    public function buildHandlerStack()
    {
        $handler = null;

        if (Coroutine::inCoroutine()) {
            $handler = new CoroutineHandler();
        }

        return $this->pushHandlers(HandlerStack::create($handler));
    }

    /**
     * Add the necessary handlers to the given handler stack.
     *
     * @param \GuzzleHttp\HandlerStack $handlerStack
     * @return \GuzzleHttp\HandlerStack
     */
    public function pushHandlers($handlerStack)
    {
        return tap($handlerStack, function ($stack) {
            $stack->push($this->buildBeforeSendingHandler());
            $stack->push($this->buildRecorderHandler());
            $stack->push($this->buildStubHandler());

            $this->middleware->each(function ($middleware) use ($stack) {
                $stack->push($middleware);
            });
        });
    }

    /**
     * Build the before sending handler.
     *
     * @return \Closure
     */
    public function buildBeforeSendingHandler()
    {
        return function ($handler) {
            return function ($request, $options) use ($handler) {
                return $handler($this->runBeforeSendingCallbacks($request, $options), $options);
            };
        };
    }

    /**
     * Build the recorder handler.
     *
     * @return \Closure
     */
    public function buildRecorderHandler()
    {
        return function ($handler) {
            return function ($request, $options) use ($handler) {
                $promise = $handler($request, $options);

                return $promise->then(function ($response) use ($request, $options) {
                    optional($this->factory)->recordRequestResponsePair(
                        (new Request($request))->withData($options['laravel_data']),
                        new Response($response)
                    );

                    return $response;
                });
            };
        };
    }

    /**
     * Build the stub handler.
     *
     * @return \Closure
     */
    public function buildStubHandler()
    {
        return function ($handler) {
            return function ($request, $options) use ($handler) {
                $response = ($this->stubCallbacks ?? collect())
                    ->map
                    ->__invoke((new Request($request))->withData($options['laravel_data']), $options)
                    ->filter()
                    ->first();

                if (is_null($response)) {
                    return $handler($request, $options);
                }

                $response = is_array($response) ? Factory::response($response) : $response;

                $sink = $options['sink'] ?? null;

                if ($sink) {
                    $response->then($this->sinkStubHandler($sink));
                }

                return $response;
            };
        };
    }

    /**
     * Execute the "before sending" callbacks.
     *
     * @param \GuzzleHttp\Psr7\RequestInterface $request
     * @return \GuzzleHttp\Psr7\RequestInterface
     */
    public function runBeforeSendingCallbacks($request, array $options)
    {
        return tap($request, function (&$request) use ($options) {
            $this->beforeSendingCallbacks->each(function ($callback) use (&$request, $options) {
                $callbackResult = call_user_func(
                    $callback,
                    (new Request($request))->withData($options['laravel_data']),
                    $options,
                    $this
                );

                if ($callbackResult instanceof RequestInterface) {
                    $request = $callbackResult;
                } elseif ($callbackResult instanceof Request) {
                    $request = $callbackResult->toPsrRequest();
                }
            });
        });
    }

    /**
     * Replace the given options with the current request options.
     *
     * @param array $options
     * @return array
     */
    public function mergeOptions(...$options)
    {
        return array_replace_recursive(
            array_merge_recursive($this->options, Arr::only($options, $this->mergableOptions)),
            ...$options
        );
    }

    /**
     * Register a stub callable that will intercept requests and be able to return stub responses.
     *
     * @param callable $callback
     * @return $this
     */
    public function stub($callback)
    {
        $this->stubCallbacks = collect($callback);

        return $this;
    }

    /**
     * Toggle asynchronicity in requests.
     *
     * @return $this
     */
    public function async(bool $async = true)
    {
        $this->async = $async;

        return $this;
    }

    /**
     * Retrieve the pending request promise.
     *
     * @return null|\GuzzleHttp\Promise\PromiseInterface
     */
    public function getPromise()
    {
        return $this->promise;
    }

    /**
     * Set the client instance.
     *
     * @return $this
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Create a new client instance using the given handler.
     *
     * @param callable $handler
     * @return $this
     */
    public function setHandler($handler)
    {
        $this->client = $this->createClient(
            $this->pushHandlers(HandlerStack::create($handler))
        );

        return $this;
    }

    /**
     * Get the pending request options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Parse multi-part form data.
     *
     * @return array|array[]
     */
    protected function parseMultipartBodyFormat(array $data)
    {
        return collect($data)->map(function ($value, $key) {
            return is_array($value) ? $value : ['name' => $key, 'contents' => $value];
        })->values()->all();
    }

    /**
     * Send an asynchronous request to the given URL.
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    protected function makePromise(string $method, string $url, array $options = [])
    {
        return $this->promise = $this->sendRequest($method, $url, $options)
            ->then(function (MessageInterface $message) {
                return tap(new Response($message), function ($response) {
                    $this->populateResponse($response);
                    $this->dispatchResponseReceivedEvent($response);
                });
            })
            ->otherwise(function (TransferException $e) {
                return $e instanceof RequestException && $e->hasResponse() ? $this->populateResponse(new Response($e->getResponse())) : $e;
            });
    }

    /**
     * Send a request either synchronously or asynchronously.
     *
     * @throws \Exception
     * @return \GuzzleHttp\Promise\PromiseInterface|\Psr\Http\Message\MessageInterface
     */
    protected function sendRequest(string $method, string $url, array $options = [])
    {
        $clientMethod = $this->async ? 'requestAsync' : 'request';

        $laravelData = $this->parseRequestData($method, $url, $options);

        return $this->buildClient()->{$clientMethod}($method, $url, $this->mergeOptions([
            'laravel_data' => $laravelData,
            'on_stats' => function ($transferStats) {
                $this->transferStats = $transferStats;
            },
        ], $options));
    }

    /**
     * Get the request data as an array so that we can attach it to the request for convenient assertions.
     *
     * @param string $method
     * @param string $url
     * @return array
     */
    protected function parseRequestData($method, $url, array $options)
    {
        if ($this->bodyFormat === 'body') {
            return [];
        }

        $laravelData = $options[$this->bodyFormat] ?? $options['query'] ?? [];

        $urlString = Str::of($url);

        if (empty($laravelData) && $method === 'GET' && $urlString->contains('?')) {
            $laravelData = (string) $urlString->after('?');
        }

        if (is_string($laravelData)) {
            parse_str($laravelData, $parsedData);

            $laravelData = is_array($parsedData) ? $parsedData : [];
        }

        return $laravelData;
    }

    /**
     * Populate the given response with additional data.
     *
     * @return Response
     */
    protected function populateResponse(Response $response)
    {
        $response->cookies = $this->cookies;

        $response->transferStats = $this->transferStats;

        return $response;
    }

    /**
     * Determine if a reusable client is required.
     *
     * @return bool
     */
    protected function requestsReusableClient()
    {
        return ! is_null($this->client) || $this->async;
    }

    /**
     * Retrieve a reusable Guzzle client.
     *
     * @return \GuzzleHttp\Client
     */
    protected function getReusableClient()
    {
        return $this->client = $this->client ?: $this->createClient($this->buildHandlerStack());
    }

    /**
     * Get the sink stub handler callback.
     *
     * @param string $sink
     * @return \Closure
     */
    protected function sinkStubHandler($sink)
    {
        return function ($response) use ($sink) {
            $body = $response->getBody()->getContents();

            if (is_string($sink)) {
                file_put_contents($sink, $body);

                return;
            }

            fwrite($sink, $body);
            rewind($sink);
        };
    }

    /**
     * Dispatch the RequestSending event if a dispatcher is available.
     */
    protected function dispatchRequestSendingEvent()
    {
        if ($dispatcher = optional($this->factory)->getDispatcher()) {
            $dispatcher->dispatch(new RequestSending($this->request));
        }
    }

    /**
     * Dispatch the ResponseReceived event if a dispatcher is available.
     */
    protected function dispatchResponseReceivedEvent(Response $response)
    {
        if (! ($dispatcher = optional($this->factory)->getDispatcher())
            || ! $this->request) {
            return;
        }

        $dispatcher->dispatch(new ResponseReceived($this->request, $response));
    }

    /**
     * Dispatch the ConnectionFailed event if a dispatcher is available.
     */
    protected function dispatchConnectionFailedEvent()
    {
        if ($dispatcher = optional($this->factory)->getDispatcher()) {
            $dispatcher->dispatch(new ConnectionFailed($this->request));
        }
    }
}
