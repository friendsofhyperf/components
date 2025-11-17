<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\RateLimit\Aspect;

use FriendsOfHyperf\RateLimit\Annotation\RateLimit;
use FriendsOfHyperf\RateLimit\Exception\RateLimitException;
use FriendsOfHyperf\RateLimit\RateLimiterFactory;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\HttpServer\Contract\RequestInterface;

class RateLimitAspect extends AbstractAspect
{
    public array $annotations = [
        RateLimit::class,
    ];

    public function __construct(
        protected RateLimiterFactory $factory,
        protected RequestInterface $request
    ) {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();
        
        /** @var RateLimit|null $annotation */
        $annotation = $metadata->method[RateLimit::class] 
            ?? $metadata->class[RateLimit::class] 
            ?? null;

        if (! $annotation) {
            return $proceedingJoinPoint->process();
        }

        $key = $this->resolveKey($annotation->key, $proceedingJoinPoint);
        $limiter = $this->factory->make($annotation->algorithm);

        if ($limiter->tooManyAttempts($key, $annotation->maxAttempts, $annotation->decay)) {
            $availableIn = $limiter->availableIn($key);
            throw new RateLimitException(
                sprintf(
                    '%s Please try again in %d seconds.',
                    $annotation->response,
                    $availableIn
                )
            );
        }

        return $proceedingJoinPoint->process();
    }

    protected function resolveKey(string $key, ProceedingJoinPoint $proceedingJoinPoint): string
    {
        if (empty($key)) {
            // Use method signature as default key
            $className = $proceedingJoinPoint->className;
            $methodName = $proceedingJoinPoint->methodName;
            $key = "{$className}:{$methodName}";
        }

        // Support placeholders like {user_id}, {ip}, etc.
        if (str_contains($key, '{')) {
            $key = preg_replace_callback('/\{([^}]+)\}/', function ($matches) use ($proceedingJoinPoint) {
                $placeholder = $matches[1];
                
                // Try to get from request
                if ($placeholder === 'ip') {
                    return $this->getClientIp();
                }
                
                if ($placeholder === 'user_id') {
                    return $this->getUserId();
                }
                
                // Try to get from method arguments
                $arguments = $proceedingJoinPoint->arguments;
                foreach ($arguments['keys'] ?? [] as $argKey => $argValue) {
                    if ($argKey === $placeholder) {
                        return (string) $argValue;
                    }
                }
                
                return $matches[0];
            }, $key);
        }

        return $key;
    }

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

    protected function getUserId(): string
    {
        // This is a placeholder - should be customized based on your auth system
        return (string) ($this->request->getAttribute('user_id') ?? 'guest');
    }
}
