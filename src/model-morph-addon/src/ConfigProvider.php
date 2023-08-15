<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ModelMorphAddon;

use FriendsOfHyperf\ModelMorphAddon\Aspect\MorphToAspect;
use FriendsOfHyperf\ModelMorphAddon\Aspect\QueriesRelationshipsAspect;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'aspects' => [
                MorphToAspect::class,
                QueriesRelationshipsAspect::class,
            ],
        ];
    }
}
