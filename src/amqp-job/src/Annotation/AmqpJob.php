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
        public ?string $pool = null,
        public ?string $queue = null,
        public int $maxAttempts = 0,
        public int $maxConsumption = 1,
        public bool $confirm = false,
        public bool $autoRegisterConsumer = true,
        public int $consumerProcessNums = 1,
    ) {
    }
}
