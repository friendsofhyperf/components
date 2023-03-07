<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Sentry\GuzzleAdapter;

use FriendsOfHyperf\Sentry\GuzzleAdapter\Exception\UnexpectedValueException;
use GuzzleHttp\Exception as GuzzleExceptions;
use GuzzleHttp\Promise\PromiseInterface;
use Http\Client\Exception as HttplugException;
use Http\Promise\Promise as HttpPromise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Wrapper around Guzzle promises.
 */
final class Promise implements HttpPromise
{
    private PromiseInterface $promise;

    /**
     *  State of the promise.
     */
    private string $state;

    private ResponseInterface $response;

    private HttplugException $exception;

    private RequestInterface $request;

    public function __construct(PromiseInterface $promise, RequestInterface $request)
    {
        $this->request = $request;
        $this->state = self::PENDING;
        $this->promise = $promise->then(function ($response) {
            $this->response = $response;
            $this->state = self::FULFILLED;

            return $response;
        }, function ($reason) use ($request) {
            $this->state = self::REJECTED;

            if ($reason instanceof HttplugException) {
                $this->exception = $reason;
            } elseif ($reason instanceof GuzzleExceptions\GuzzleException) {
                $this->exception = $this->handleException($reason, $request);
            } elseif ($reason instanceof Throwable) {
                $this->exception = new HttplugException\TransferException('Invalid exception returned from Guzzle6', 0, $reason);
            } else {
                $this->exception = new UnexpectedValueException('Reason returned from Guzzle6 must be an Exception');
            }

            throw $this->exception;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        return new static($this->promise->then($onFulfilled, $onRejected), $this->request);
    }

    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * {@inheritdoc}
     */
    public function wait($unwrap = true)
    {
        $this->promise->wait(false);

        if ($unwrap) {
            if ($this->getState() == self::REJECTED) {
                throw $this->exception;
            }

            return $this->response;
        }
    }

    /**
     * Converts a Guzzle exception into an Httplug exception.
     */
    private function handleException(GuzzleExceptions\GuzzleException $exception, RequestInterface $request): HttplugException
    {
        // if ($exception instanceof GuzzleExceptions\SeekException) {
        //     return new HttplugException\RequestException($exception->getMessage(), $request, $exception);
        // }

        if ($exception instanceof GuzzleExceptions\ConnectException) {
            return new HttplugException\NetworkException($exception->getMessage(), $exception->getRequest(), $exception);
        }

        if ($exception instanceof GuzzleExceptions\RequestException) {
            // Make sure we have a response for the HttpException
            if ($exception->hasResponse()) {
                return new HttplugException\HttpException(
                    $exception->getMessage(),
                    $exception->getRequest(),
                    $exception->getResponse(),
                    $exception
                );
            }

            return new HttplugException\RequestException($exception->getMessage(), $exception->getRequest(), $exception);
        }

        return new HttplugException\TransferException($exception->getMessage(), 0, $exception);
    }
}
