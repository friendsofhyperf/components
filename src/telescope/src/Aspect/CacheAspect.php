<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope\Aspect;

use FriendsOfHyperf\Telescope\IncomingEntry;
use FriendsOfHyperf\Telescope\Telescope;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\AnnotationManager;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

use function Hyperf\Tappable\tap;

class CacheAspect extends AbstractAspect
{
    public array $classes = [];

    public array $annotations = [
        Cacheable::class,
    ];

    public function __construct(protected AnnotationManager $annotationManager)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($result) use ($proceedingJoinPoint) {
            $className = $proceedingJoinPoint->className;
            $method = $proceedingJoinPoint->methodName;
            $arguments = $proceedingJoinPoint->arguments['keys'];
            [$key, $ttl, $group, $annotation] = $this->annotationManager->getCacheableValue($className, $method, $arguments);

            Telescope::recordCache(IncomingEntry::make([
                'type' => 'hit',
                'key' => $key,
                'value' => $result,
            ]));

            return $result;
        });
    }
}
