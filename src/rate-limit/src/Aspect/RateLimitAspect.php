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

class RateLimitAspect extends AbstractAspect
{
    public array $annotations = [
        RateLimit::class,
    ];

    public function __construct(
        protected RateLimiterFactory $factory,
    ) {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();

        /** @var null|RateLimit $annotation */
        $annotation = $metadata->method[RateLimit::class] ?? null;

        if (! $annotation) {
            return $proceedingJoinPoint->process();
        }

        $key = $this->resolveKey($annotation->key, $proceedingJoinPoint);
        $limiter = $this->factory->make($annotation->algorithm, $annotation->pool);

        if ($limiter->tooManyAttempts($key, $annotation->maxAttempts, $annotation->decay)) {
            $availableIn = $limiter->availableIn($key);
            throw new RateLimitException(
                sprintf(
                    '%s Please try again in %d seconds.',
                    $annotation->response,
                    $availableIn
                ),
                $annotation->responseCode
            );
        }

        return $proceedingJoinPoint->process();
    }

    protected function resolveKey(string|array $key, ProceedingJoinPoint $proceedingJoinPoint): string
    {
        if (empty($key)) {
            // Use method signature as default key
            $className = $proceedingJoinPoint->className;
            $methodName = $proceedingJoinPoint->methodName;
            $key = "{$className}:{$methodName}";
        }

        if (is_callable($key)) {
            return $key($proceedingJoinPoint);
        }

        // Support placeholders like {user_id}, {ip}, etc.
        if (str_contains($key, '{')) {
            $key = preg_replace_callback('/\{([^}]+)\}/', function ($matches) use ($proceedingJoinPoint) {
                $placeholder = $matches[1];

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
}
