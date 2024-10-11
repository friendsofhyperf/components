<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\AmqpJob\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_CLASS)]
class AmqpJob extends AbstractAnnotation
{
    public function __construct(
        public string $exchange,
        public string $routingKey,
        public string $pool = 'default',
        public int $nums = 1,
        public bool $enable = true,
        public int $maxConsumption = 1
    ) {
    }
}
