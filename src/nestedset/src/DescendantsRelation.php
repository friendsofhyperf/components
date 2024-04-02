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

use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Relations\Constraint;

class DescendantsRelation extends BaseRelation
{
    /**
     * Set the base constraints on the relation query.
     */
    public function addConstraints()
    {
        if (! Constraint::isConstraint()) {
            return;
        }

        $this->query->whereDescendantOf($this->parent)
            ->applyNestedSetScope();
    }

    /**
     * @param QueryBuilder $query
     * @param Model $model
     */
    protected function addEagerConstraint($query, $model)
    {
        $query->orWhereDescendantOf($model);
    }

    /**
     * @param mixed $related
     * @return mixed
     */
    protected function matches(Model $model, $related)
    {
        return $related->isDescendantOf($model);
    }

    /**
     * @param mixed $hash
     * @param mixed $table
     * @param mixed $lft
     * @param mixed $rgt
     * @return string
     */
    protected function relationExistenceCondition($hash, $table, $lft, $rgt)
    {
        return "{$hash}.{$lft} between {$table}.{$lft} + 1 and {$table}.{$rgt}";
    }
}
