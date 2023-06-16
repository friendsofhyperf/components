<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\FastPaginate;

use Hyperf\Database\Model\Relations\BelongsToMany;
use Hyperf\Database\Model\Relations\HasManyThrough;

use function Hyperf\Tappable\tap;

/**
 * @mixin \Hyperf\Database\Model\Relations\Relation
 */
class RelationMixin
{
    public function fastPaginate()
    {
        return function ($perPage = null, $columns = ['*'], $pageName = 'page', $page = null) {
            /** @var \Hyperf\Database\Model\Relations\Relation $this */
            if ($this instanceof HasManyThrough || $this instanceof BelongsToMany) {
                /* @phpstan-ignore-next-line */
                $this->query->addSelect($this->shouldSelect($columns));
            }

            /* @phpstan-ignore-next-line */
            return tap($this->query->fastPaginate($perPage, $columns, $pageName, $page), function ($paginator) {
                if ($this instanceof BelongsToMany) {
                    /* @phpstan-ignore-next-line */
                    $this->hydratePivotRelation($paginator->items());
                }
            });
        };
    }

    public function simpleFastPaginate()
    {
        return function ($perPage = null, $columns = ['*'], $pageName = 'page', $page = null) {
            /** @var \Hyperf\Database\Model\Relations\Relation $this */
            if ($this instanceof HasManyThrough || $this instanceof BelongsToMany) {
                /* @phpstan-ignore-next-line */
                $this->query->addSelect($this->shouldSelect($columns));
            }

            /* @phpstan-ignore-next-line */
            return tap($this->query->simpleFastPaginate($perPage, $columns, $pageName, $page), function ($paginator) {
                if ($this instanceof BelongsToMany) {
                    /* @phpstan-ignore-next-line */
                    $this->hydratePivotRelation($paginator->items());
                }
            });
        };
    }
}
