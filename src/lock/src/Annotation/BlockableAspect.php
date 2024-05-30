<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Lock\Annotation;

use FriendsOfHyperf\Lock\LockFactory;
use Hyperf\Cache\Helper\StringHelper;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

class BlockableAspect extends AbstractAspect
{
    public array $annotations = [
        Blockable::class,
    ];

    public function __construct(private LockFactory $lockFactory)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $arguments = $proceedingJoinPoint->arguments['keys'] ?? [];
        $annotationMetadata = $proceedingJoinPoint->getAnnotationMetadata();
        /** @var Blockable|null $annotation */
        $annotation = $annotationMetadata->method[Blockable::class] ?? null;

        if (! $annotation || $annotation->seconds <= 0) {
            return $proceedingJoinPoint->process();
        }

        $key = StringHelper::format($annotation->prefix, $arguments, $annotation->value);

        return $this->lockFactory->make($key, $annotation->ttl, driver: $annotation->driver)
            ->block($annotation->seconds, fn () => $proceedingJoinPoint->process());
    }
}
