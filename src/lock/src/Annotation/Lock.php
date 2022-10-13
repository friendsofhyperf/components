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

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class Lock extends AbstractAnnotation
{
    public function __construct(
        public string $name,
        public int $seconds = 0,
        public ?string $owner = null,
        public string $driver = 'default',
        public ?int $block = null,
        public mixed $failCallback = null
    ) {
    }
}
