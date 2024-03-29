<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ModelObserver\Annotation;

use Attribute;
use Hyperf\Database\Model\Model;
use Hyperf\Di\Annotation\AbstractMultipleAnnotation;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Observer extends AbstractMultipleAnnotation
{
    /**
     * @param string|string[] $model
     */
    public function __construct(
        /**
         * @var class-string<Model>|class-string<Model>[]
         */
        public string|array $model,
        public int $priority = 0
    ) {
    }
}
