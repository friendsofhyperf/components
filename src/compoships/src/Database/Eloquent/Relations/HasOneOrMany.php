<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Compoships\Database\Eloquent\Relations;

use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Relations\Constraint;
use Hyperf\Database\Query\JoinClause;

use function Hyperf\Collection\collect;
use function Hyperf\Collection\last;
use function Hyperf\Tappable\tap;

trait HasOneOrMany
{
    /**
     * Set the base constraints on the relation query.
     */
    public function addConstraints()
    {
        if (Constraint::isConstraint()) {
            $foreignKey = $this->getForeignKeyName();
            $parentKeyValue = $this->getParentKey();

            // If the foreign key is an array (multi-column relationship), we adjust the query.
            if (is_array($this->foreignKey)) {
                $allParentKeyValuesAreNull = array_unique($parentKeyValue) === [null];

                foreach ($this->foreignKey as $index => $key) {
                    $tmp = explode('.', $key);
                    $key = end($tmp);
                    $fullKey = $this->getRelated()
                        ->getTable() . '.' . $key;
                    $this->query->where($fullKey, '=', $parentKeyValue[$index]);

                    if ($allParentKeyValuesAreNull) {
                        $this->query->whereNotNull($fullKey);
                    }
                }
            } else {
                parent::addConstraints();
            }
        }
    }

    /**
     * Get the fully qualified parent key name.
     *
     * @return string
     */
    public function getQualifiedParentKeyName()
    {
        if (is_array($this->localKey)) { // Check for multi-columns relationship
            return array_map(function ($k) {
                return $this->parent->getTable() . '.' . $k;
            }, $this->localKey);
        } else {
            return $this->parent->getTable() . '.' . $this->localKey;
        }
    }

    /**
     * Get the plain foreign key.
     *
     * @return string
     */
    public function getForeignKeyName()
    {
        $key = $this->getQualifiedForeignKeyName();

        if (is_array($key)) { // Check for multi-columns relationship
            return array_map(function ($k) {
                $segments = explode('.', $k);

                return $segments[count($segments) - 1];
            }, $key);
        } else {
            $segments = explode('.', $key);

            return $segments[count($segments) - 1];
        }
    }

    /**
     * Attach a model instance to the parent model.
     *
     * @return Model
     */
    public function save(Model $model)
    {
        $foreignKey = $this->getForeignKeyName();
        $parentKeyValue = $this->getParentKey();

        if (is_array($foreignKey)) { // Check for multi-columns relationship
            foreach ($foreignKey as $index => $key) {
                $model->setAttribute($key, $parentKeyValue[$index]);
            }
        } else {
            $model->setAttribute($foreignKey, $parentKeyValue);
        }

        return $model->save() ? $model : false;
    }

    /**
     * Create a new instance of the related model.
     *
     * @return Model
     */
    public function create(array $attributes = [])
    {
        return tap($this->related->newInstance($attributes), function ($instance) {
            $foreignKey = $this->getForeignKeyName();
            $parentKeyValue = $this->getParentKey();

            if (is_array($foreignKey)) { // Check for multi-columns relationship
                foreach ($foreignKey as $index => $key) {
                    $instance->setAttribute($key, $parentKeyValue[$index]);
                }
            } else {
                $instance->setAttribute($foreignKey, $parentKeyValue);
            }

            $instance->save();
        });
    }

    /**
     * Add the constraints for a relationship query on the same table.
     *
     * @param array|mixed $columns
     *
     * @return Builder
     */
    public function getRelationExistenceQueryForSelfRelation(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        $query->from($query->getModel()
            ->getTable() . ' as ' . $hash = $this->getRelationCountHash());

        $query->getModel()
            ->setTable($hash);

        return $query->select($columns)
            ->whereColumn(
                $this->getQualifiedParentKeyName(),
                '=',
                is_array($this->getForeignKeyName()) // Check for multi-columns relationship
                    ? array_map(function ($k) use ($hash) {
                        return $hash . '.' . $k;
                    }, $this->getForeignKeyName()) : $hash . '.' . $this->getForeignKeyName()
            );
    }

    /**
     * Add join query constraints for one of many relationships.
     */
    public function addOneOfManyJoinSubQueryConstraints(JoinClause $join)
    {
        if (is_array($this->foreignKey)) {
            foreach ($this->foreignKey as $key) {
                $join->on($this->qualifySubSelectColumn($key), '=', $this->qualifyRelatedColumn($key));
            }
        } else {
            parent::addOneOfManyJoinSubQueryConstraints($join);
        }
    }

    /**
     * Get the name of the "where in" method for eager loading.
     *
     * @param array|string $key
     *
     * @return string
     *
     * 5.6 - no method \FriendsOfHyperf\Compoships\Database\Eloquent\Relations\HasOneOrMany::whereInMethod
     * added in this commit (5.7.17) https://github.com/illuminate/database/commit/9af300d1c50c9ec526823c1e6548daa3949bf9a9
     */
    protected function whereInMethod(Model $model, $key)
    {
        if (! is_array($key)) {
            return parent::whereInMethod($model, $key);
        }

        $where = collect($key)->filter(function ($key) use ($model) {
            return $model->getKeyName() === last(explode('.', $key))
                && in_array($model->getKeyType(), ['int', 'integer']);
        });

        return $where->count() === count($key) ? 'whereIntegerInRaw' : 'whereIn';
    }

    /**
     * Match the eagerly loaded results to their many parents.
     *
     * @param string $relation
     * @param string $type
     *
     * @return array
     */
    protected function matchOneOrMany(array $models, Collection $results, $relation, $type)
    {
        $dictionary = $this->buildDictionary($results);

        // Once we have the dictionary we can simply spin through the parent models to
        // link them up with their children using the keyed dictionary to make the
        // matching very convenient and easy work. Then we'll just return them.
        foreach ($models as $model) {
            $key = $model->getAttribute($this->localKey);
            // If the foreign key is an array, we know it's a multi-column relationship
            // And we join the values to construct the dictionary key
            $dictKey = is_array($key) ? implode('-', $key) : $key;

            if (isset($dictionary[$dictKey])) {
                $model->setRelation($relation, $this->getRelationValue($dictionary, $dictKey, $type));
            }
        }

        return $models;
    }

    /**
     * Build model dictionary keyed by the relation's foreign key.
     *
     * @return array
     */
    protected function buildDictionary(Collection $results)
    {
        $dictionary = [];

        $foreign = $this->getForeignKeyName();

        // First we will create a dictionary of models keyed by the foreign key of the
        // relationship as this will allow us to quickly access all of the related
        // models without having to do nested looping which will be quite slow.
        foreach ($results as $result) {
            // If the foreign key is an array, we know it's a multi-column relationship...
            if (is_array($foreign)) {
                $dictKeyValues = array_map(function ($k) use ($result) {
                    return $result->{$k};
                }, $foreign);
                // ... so we join the values to construct the dictionary key
                $dictionary[implode('-', $dictKeyValues)][] = $result;
            } else {
                $dictionary[$result->{$foreign}][] = $result;
            }
        }

        return $dictionary;
    }

    /**
     * Set the foreign ID for creating a related model.
     */
    protected function setForeignAttributesForCreate(Model $model)
    {
        $foreignKey = $this->getForeignKeyName();
        $parentKeyValue = $this->getParentKey();
        if (is_array($foreignKey)) { // Check for multi-columns relationship
            foreach ($foreignKey as $index => $key) {
                $model->setAttribute($key, $parentKeyValue[$index]);
            }
        } else {
            parent::setForeignAttributesForCreate($model);
        }
    }
}
