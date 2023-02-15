<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\FastPaginate\Listener;

use FriendsOfHyperf\FastPaginate\BuilderMixin;
use FriendsOfHyperf\FastPaginate\RelationMixin;
use FriendsOfHyperf\FastPaginate\ScoutMixin;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\Relation;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;

class RegisterMixinListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    /**
     * @param BootApplication $event
     */
    public function process(object $event): void
    {
        Builder::mixin(new BuilderMixin());
        Relation::mixin(new RelationMixin());

        if (class_exists(\Hyperf\Scout\Builder::class)) {
            \Hyperf\Scout\Builder::mixin(new ScoutMixin());
        }
    }
}
