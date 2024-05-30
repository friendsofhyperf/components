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

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_METHOD)]
class Blockable extends AbstractAnnotation
{
    public function __construct(
        public ?string $prefix = null,
        public ?string $value = null,
        public int $seconds = 0,
        public int $ttl = 0,
        public string $driver = 'default'
    ) {
    }
}
