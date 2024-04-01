<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Nestedset;

use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Relations\Constraint;

class DescendantsRelation extends BaseRelation
{
    /**
     * Set the base constraints on the relation query.
     */
    public function addConstraints(): void
    {
        if (! Constraint::isConstraint()) {
            return;
        }
        $this->query->whereDescendantOf($this->parent)
            ->applyNestedSetScope();
    }

    protected function addEagerConstraint(Builder $query, Model $model): void
    {
        $query->orWhereDescendantOf($model);
    }

    protected function matches(Model $model, $related): mixed
    {
        return $related->isDescendantOf($model);
    }

    protected function relationExistenceCondition(
        string $hash,
        string $table,
        string $lft,
        string $rgt
    ): string {
        return "{$hash}.{$lft} between {$table}.{$lft} + 1 and {$table}.{$rgt}";
    }
}
