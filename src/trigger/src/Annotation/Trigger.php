<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Trigger\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_CLASS)]
class Trigger extends AbstractAnnotation
{
    public function __construct(
        public ?string $database = null,
        public ?string $table = null,
        public array $events = ['*'],
        public string $connection = 'default',
        public int $priority = 0
    ) {
    }
}
