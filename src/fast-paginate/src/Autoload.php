<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\FastPaginate\BuilderMixin;
use FriendsOfHyperf\FastPaginate\RelationMixin;
use FriendsOfHyperf\FastPaginate\ScoutMixin;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\Relation;

Builder::mixin(new BuilderMixin());
Relation::mixin(new RelationMixin());

if (class_exists(\Hyperf\Scout\Builder::class)) {
    \Hyperf\Scout\Builder::mixin(new ScoutMixin());
}
