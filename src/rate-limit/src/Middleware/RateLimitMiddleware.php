<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\RateLimit\Middleware;

use FriendsOfHyperf\RateLimit\Algorithm;
use FriendsOfHyperf\RateLimit\RateLimiterFactory;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class RateLimitMiddleware implements MiddlewareInterface
{
    /**
     * Maximum number of attempts allowed.
     */
    protected int $maxAttempts = 60;

    /**
     * Time window in seconds.
     */
    protected int $decay = 60;

    /**
     * Algorithm to use: fixed_window, sliding_window, token_bucket, leaky_bucket.
     */
    protected Algorithm $algorithm = Algorithm::FIXED_WINDOW;

    /**
     * Redis connection pool to use.
     */
    protected ?string $pool = null;

    /**
     * Response message when rate limit exceeded.
     */
    protected string $responseMessage = 'Too Many Attempts.';

    /**
     * Response code when rate limit exceeded.
     */
    protected int $responseCode = 429;

    public function __construct(
        protected ContainerInterface $container,
        protected RequestInterface $request,
        protected HttpResponse $response,
        protected RateLimiterFactory $factory
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $key = $this->resolveKey($request);
        $limiter = $this->factory->make($this->algorithm, $this->pool);

        if ($limiter->tooManyAttempts($key, $this->maxAttempts, $this->decay)) {
            return $this->buildRateLimitExceededResponse($key, $limiter->availableIn($key));
        }

        $response = $handler->handle($request);

        return $this->addHeaders(
            $response,
            $this->maxAttempts,
            $limiter->remaining($key, $this->maxAttempts),
            $limiter->availableIn($key)
        );
    }

    /**
     * Resolve the rate limit key.
     */
    protected function resolveKey(ServerRequestInterface $request): string
    {
        // Default key based on IP address
        return 'rate_limit:' . $this->getClientIp();
    }

    /**
     * Get the client IP address.
     */
    protected function getClientIp(): string
    {
        $headers = [
            'x-forwarded-for',
            'x-real-ip',
            'remote-addr',
        ];

        foreach ($headers as $header) {
            if ($ip = $this->request->getHeaderLine($header)) {
                // Get first IP if comma-separated list
                if (str_contains($ip, ',')) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                return $ip;
            }
        }

        return $this->request->server('remote_addr', 'unknown');
    }

    /**
     * Build rate limit exceeded response.
     */
    protected function buildRateLimitExceededResponse(string $key, int $retryAfter): ResponseInterface
    {
        return $this->response
            ->json([
                'message' => $this->responseMessage,
                'retry_after' => $retryAfter,
            ])
            ->withStatus($this->responseCode)
            ->withAddedHeader('Retry-After', (string) $retryAfter)
            ->withAddedHeader('X-RateLimit-Limit', (string) $this->maxAttempts)
            ->withAddedHeader('X-RateLimit-Remaining', '0')
            ->withAddedHeader('X-RateLimit-Reset', (string) (time() + $retryAfter));
    }

    /**
     * Add rate limit headers to response.
     */
    protected function addHeaders(
        ResponseInterface $response,
        int $maxAttempts,
        int $remaining,
        int $retryAfter
    ): ResponseInterface {
        return $response
            ->withHeader('X-RateLimit-Limit', (string) $maxAttempts)
            ->withHeader('X-RateLimit-Remaining', (string) max(0, $remaining))
            ->withHeader('X-RateLimit-Reset', (string) (time() + $retryAfter));
    }
}
