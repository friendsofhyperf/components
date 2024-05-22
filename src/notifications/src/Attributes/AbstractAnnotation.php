<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notifications\Attributes;

use Hyperf\Di\Annotation\AbstractAnnotation as Base;

class AbstractAnnotation extends Base
{
    public const PREFIX = 'annotation';

    public function __construct(
        public string $name
    ) {
    }

    public function collectClass(string $className): void
    {
        AttributesCollector::collect(static::PREFIX . $this->name, $className);
    }

    public static function get(string $name, mixed $default = null): ?string
    {
        return AttributesCollector::get(static::PREFIX . '.' . $name, $default);
    }
}
