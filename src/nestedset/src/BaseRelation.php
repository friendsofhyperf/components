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

use Hyperf\Database\Model\Builder as EloquentBuilder;
use Hyperf\Database\Model\Collection as EloquentCollection;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Relations\Relation;
use Hyperf\Database\Query\Builder;
use InvalidArgumentException;

use function Hyperf\Support\optional;

abstract class BaseRelation extends Relation
{
    /**
     * @var QueryBuilder
     */
    protected $query;

    /**
     * @var NodeTrait|Model
     */
    protected $parent;

    /**
     * The count of self joins.
     *
     * @var int
     */
    protected static $selfJoinCount = 0;

    /**
     * AncestorsRelation constructor.
     */
    public function __construct(QueryBuilder $builder, Model $model)
    {
        if (! NestedSet::isNode($model)) {
            throw new InvalidArgumentException('Model must be node.');
        }

        parent::__construct($builder, $model);
    }

    /**
     * @param array $columns
     *
     * @return mixed
     */
    public function getRelationExistenceQuery(
        EloquentBuilder $query,
        EloquentBuilder $parent,
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
     *
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        return $models;
    }

    /**
     * @return mixed
     */
    public function getRelationQuery(
        EloquentBuilder $query,
        EloquentBuilder $parent,
        array $columns = ['*']
    ) {
        return $this->getRelationExistenceQuery($query, $parent, $columns);
    }

    /**
     * Get a relationship join table hash.
     */
    public function getRelationCountHash(bool $incrementJoinCount = true): string
    {
        return 'nested_set_' . ($incrementJoinCount ? static::$selfJoinCount++ : static::$selfJoinCount);
    }

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults(): EloquentCollection
    {
        return $this->query->get();
    }

    /**
     * Set the constraints for an eager load of the relation.
     */
    public function addEagerConstraints(array $models): void
    {
        // The first model in the array is always the parent, so add the scope constraints based on that model.
        // @link https://github.com/laravel/framework/pull/25240
        // @link https://github.com/lazychaser/laravel-nestedset/issues/351
        optional(reset($models))->applyNestedSetScope($this->query);

        $this->query->whereNested(function (Builder $inner) use ($models) {
            // We will use this query in order to apply constraints to the
            // base query builder
            $outer = $this->parent->newQuery()->setQuery($inner);

            foreach ($models as $model) {
                $this->addEagerConstraint($outer, $model);
            }
        });
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param string $relation
     */
    public function match(array $models, EloquentCollection $results, $relation): array
    {
        foreach ($models as $model) {
            $related = $this->matchForModel($model, $results);

            $model->setRelation($relation, $related);
        }

        return $models;
    }

    /**
     * @param mixed $related
     * @return bool
     */
    abstract protected function matches(Model $model, $related);

    /**
     * @param QueryBuilder $query
     * @param Model $model
     */
    abstract protected function addEagerConstraint($query, $model);

    /**
     * @param mixed $hash
     * @param mixed $table
     * @param mixed $lft
     * @param mixed $rgt
     * @return string
     */
    abstract protected function relationExistenceCondition($hash, $table, $lft, $rgt);

    protected function matchForModel(Model $model, EloquentCollection $results): EloquentCollection
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
