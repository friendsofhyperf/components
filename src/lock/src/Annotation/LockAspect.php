<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Lock\Annotation;

use FriendsOfHyperf\Lock\Driver\LockInterface;
use FriendsOfHyperf\Lock\Exception\GetLockFailsException;
use FriendsOfHyperf\Lock\Exception\LockTimeoutException;
use FriendsOfHyperf\Lock\LockFactory;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

class LockAspect extends AbstractAspect
{
    public array $annotations = [
        Lock::class,
    ];

    public function __construct(protected LockFactory $factory)
    {
        $this->factory = $factory;
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $annotation = $this->getAnnotation($proceedingJoinPoint->className, $proceedingJoinPoint->methodName, Lock::class);

        if ($annotation instanceof Lock) {
            $lock = $this->makeLockByAnnotation($annotation);

            try {
                if ($annotation->block) {
                    $lock->block($annotation->block);
                } else {
                    if (! $lock->get()) {
                        throw new GetLockFailsException('Lock fails.');
                    }

                    return $proceedingJoinPoint->process();
                }
            } catch (GetLockFailsException|LockTimeoutException $e) {
                if ($annotation->failCallback && is_callable($annotation->failCallback)) {
                    return call_user_func($annotation->failCallback, $proceedingJoinPoint);
                }

                throw $e;
            } finally {
                $lock->release();
            }
        }

        return $proceedingJoinPoint->process();
    }

    protected function getAnnotation(string $className, string $method, string $annotation): ?Lock
    {
        $collector = AnnotationCollector::get($className);
        return $collector['_m'][$method][$annotation] ?? null;
    }

    protected function makeLockByAnnotation(Lock $annotation): LockInterface
    {
        return $this->factory->make(
            name: $annotation->name,
            seconds: $annotation->seconds,
            owner: $annotation->owner,
            driver: $annotation->driver
        );
    }
}
