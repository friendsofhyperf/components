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

use FriendsOfHyperf\RateLimit\Annotation\AutoSort;
use FriendsOfHyperf\RateLimit\Annotation\RateLimit;
use FriendsOfHyperf\RateLimit\Exception\RateLimitException;
use FriendsOfHyperf\RateLimit\RateLimiterFactory;
use Hyperf\Di\Annotation\MultipleAnnotation;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Stdlib\SplPriorityQueue;
use Hyperf\Stringable\Str;

use function Hyperf\Collection\data_get;

class RateLimitAspect extends AbstractAspect
{
    public array $annotations = [
        RateLimit::class,
    ];

    public function __construct(protected RateLimiterFactory $factory)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();

        /** @var null|MultipleAnnotation $annotations */
        $annotations = $metadata->method[RateLimit::class] ?? null;
        $isAutoSort = isset($metadata->method[AutoSort::class]);

        /** @var SplPriorityQueue<RateLimit> $queue */
        $queue = new SplPriorityQueue();
        foreach ($annotations?->toAnnotations() ?? [] as $annotation) {
            /** @var RateLimit $annotation */
            $priority = 0;
            if ($isAutoSort) {
                $priority = 0 - $annotation->maxAttempts / $annotation->decay;
            }
            $queue->insert($annotation, $priority);
        }

        foreach ($queue as $annotation) {
            /** @var RateLimit $annotation */
            $key = $this->resolveKey($annotation->key, $proceedingJoinPoint);
            $limiter = $this->factory->make($annotation->algorithm, $annotation->pool);

            if ($limiter->tooManyAttempts($key, $annotation->maxAttempts, $annotation->decay)) {
                $availableIn = $limiter->availableIn($key);
                $message = Str::replaceArray('%d', [(string) $availableIn], $annotation->response);

                throw new RateLimitException(
                    $message,
                    $annotation->responseCode,
                    $availableIn
                );
            }
        }

        return $proceedingJoinPoint->process();
    }

    protected function resolveKey(string|array $key, ProceedingJoinPoint $proceedingJoinPoint): string
    {
        if (empty($key)) { // Use method signature as default key
            $className = $proceedingJoinPoint->className;
            $methodName = $proceedingJoinPoint->methodName;
            return "{$className}:{$methodName}";
        }

        if (is_callable($key)) {
            return $key($proceedingJoinPoint);
        }

        if (is_array($key)) {
            $key = implode(':', array_values($key));
        }

        // Support placeholders like {user_id}, {ip}, etc.
        if (str_contains($key, '{')) {
            $key = preg_replace_callback('/\{([^}]+)\}/', function ($matches) use ($proceedingJoinPoint) {
                $placeholder = $matches[1];

                /** @var array $arguments */
                $arguments = $proceedingJoinPoint->arguments['keys'] ?? [];
                foreach ($arguments as $argKey => $argValue) {
                    if (
                        (is_array($argValue) || is_object($argValue))
                        && str_contains($placeholder, '.')
                    ) {
                        return (string) data_get($arguments, $placeholder);
                    }
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
