<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ModelMorphAddon\Aspect;

use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Relations\MorphTo;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

/**
 * 临时解决方案.
 */
#[Aspect()]
class MorphToAspect extends AbstractAspect
{
    public $classes = [
        MorphTo::class . '::createModelByType',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $arguments = $proceedingJoinPoint->getArguments();
        $type = $arguments[0];
        $instance = $proceedingJoinPoint->getInstance();
        /** @var Model $parent */
        $parent = $instance->getChild();
        $class = $parent::getActualClassNameForMorph($type);

        return new $class();
    }
}
