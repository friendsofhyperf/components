<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ModelScope\Annotation;

use Attribute;
use Hyperf\Database\Model\Scope;
use Hyperf\Di\Annotation\AbstractMultipleAnnotation;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class ScopedBy extends AbstractMultipleAnnotation
{
    public function __construct(
        /**
         * @var class-string<Scope>|class-string<Scope>[]
         */
        public string|array $classes,
        public int $priority = 0
    ) {
    }
}
