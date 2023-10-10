<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ValidatedDTO\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Cast
{
    public function __construct(
        /**
         * @var class-string
         */
        public string $type,
        /**
         * @var class-string
         */
        public ?string $param = null,
    ) {
    }
}
