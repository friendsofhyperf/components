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
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Collection as EloquentCollection;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Relations\Relation;
use InvalidArgumentException;

abstract class BaseRelation extends Relation
{
    protected int $selfJoinCount = 0;

    public function __construct(Builder $query, Model $parent)
    {
        if (! NestedSet::isNode($parent)) {
            throw new InvalidArgumentException('Model must be node.');
        }
        parent::__construct($query, $parent);
    }

    public function getRelationExistenceQuery(
        Builder $query,
        Builder $parentQuery,
        $columns = ['*']
    ) {
        $query = $this->getParent()->replicate()->newScopedQuery()->select($columns);

        $table = $query->getModel()->getTable();

        $query->from($table . ' as ' . $hash = $this->getRelationCountHash());

        $query->getModel()->setTable($hash);

        $grammar = $query->getQuery()->getGrammar();

        $condition = $this->relationExistenceCondition(
            $grammar->wrapTable($hash),
            $grammar->wrapTable($table),
            $grammar->wrap($this->parent->getLftName()),
            $grammar->wrap($this->parent->getRgtName())
        );

        return $query->whereRaw($condition);
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param string $relation
     */
    public function initRelation(array $models, $relation): array
    {
        return $models;
    }

    /**
     * Get a relationship join table hash.
     */
    public function getRelationCountHash(bool $incrementJoinCount = true): string
    {
        return 'nested_set_' . ($incrementJoinCount ? $this->selfJoinCount++ : $this->selfJoinCount);
    }

    public function getResults(): Collection
    {
        return $this->query->get();
    }

    /**
     * Set the constraints for an eager load of the relation.
     */
    public function addEagerConstraints(array $models): void
    {
        $this->query->whereNested(function (Builder $inner) use ($models) {
            // We will use this query in order to apply constraints to the
            // base query builder
            $outer = $this->parent->newQuery()->setQuery($inner->getQuery());

            foreach ($models as $model) {
                $this->addEagerConstraint($outer, $model);
            }
        });
    }

    /**
     * Match the eagerly loaded results to their parents.
     * @param mixed $relation
     */
    public function match(array $models, EloquentCollection $results, $relation)
    {
        foreach ($models as $model) {
            $related = $this->matchForModel($model, $results);

            $model->setRelation($relation, $related);
        }

        return $models;
    }

    /**
     * Get the plain foreign key.
     *
     * @return mixed
     */
    public function getForeignKeyName(): string
    {
        return NestedSet::PARENT_ID;
    }

    abstract protected function matches(Model $model, $related): bool;

    abstract protected function addEagerConstraint(Builder $query, Model $model): void;

    abstract protected function relationExistenceCondition(
        string $hash,
        string $table,
        string $lft,
        string $rgt
    ): string;

    /**
     * @return Collection
     */
    protected function matchForModel(Model $model, EloquentCollection $results)
    {
        $result = $this->related->newCollection();

        foreach ($results as $related) {
            if ($this->matches($model, $related)) {
                $result->push($related);
            }
        }

        return $result;
    }
}
