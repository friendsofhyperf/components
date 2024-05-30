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

use Hyperf\Cache\Helper\StringHelper;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

use function FriendsOfHyperf\Lock\lock;

class LockableAspect extends AbstractAspect
{
    public array $annotations = [
        Lockable::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $arguments = $proceedingJoinPoint->arguments['keys'];
        $annotationMetadata = $proceedingJoinPoint->getAnnotationMetadata();
        /** @var Lockable $annotation */
        $annotation = $annotationMetadata->method[Lockable::class];

        $key = $this->getFormattedKey($annotation->prefix, $arguments, $annotation->value);
        $lock = lock($key, $annotation->ttl, null, $annotation->driver);
        $lock->block($annotation->waitSeconds);
        try {
            return $proceedingJoinPoint->process();
        } finally {
            $lock->release();
        }
    }

    protected function getFormattedKey(string $prefix, array $arguments, ?string $value = null): string
    {
        return StringHelper::format($prefix, $arguments, $value);
    }
}
