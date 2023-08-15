<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Once\Aspect;

use FriendsOfHyperf\Once\Annotation\Once;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Spatie\Once\Cache;

use function Hyperf\Tappable\tap;

class OnceAspect extends AbstractAspect
{
    public array $annotations = [
        Once::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $cache = Cache::getInstance();
        $object = $proceedingJoinPoint->getInstance();
        $arguments = $proceedingJoinPoint->getArguments();
        $hash = md5(
            $proceedingJoinPoint->className .
            $proceedingJoinPoint->methodName .
            serialize(
                array_map(
                    fn ($argument) => is_object($argument) ? spl_object_hash($argument) : $argument,
                    $arguments
                )
            )
        );

        if ($cache->has($object, $hash)) {
            return $cache->get($object, $hash);
        }

        return tap($proceedingJoinPoint->process(), fn ($result) => $cache->set($object, $hash, $result));
    }
}
