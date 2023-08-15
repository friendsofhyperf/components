<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Compoships;

use FriendsOfHyperf\Compoships\Database\Eloquent\Concerns\HasRelationships;
use FriendsOfHyperf\Compoships\Database\Query\Builder as QueryBuilder;

trait Compoships
{
    use HasRelationships;

    public function getAttribute($key)
    {
        if (is_array($key)) { // Check for multi-columns relationship
            return array_map(function ($k) {
                return parent::getAttribute($k);
            }, $key);
        }

        return parent::getAttribute($key);
    }

    public function qualifyColumn($column)
    {
        if (is_array($column)) { // Check for multi-column relationship
            return array_map(function ($c) {
                if (str_contains($c, '.')) {
                    return $c;
                }

                return $this->getTable() . '.' . $c;
            }, $column);
        }

        return parent::qualifyColumn($column);
    }

    /**
     * Configure Eloquent to use Compoships Query Builder.
     *
     * @return \FriendsOfHyperf\Compoships\Database\Query\Builder|static
     */
    protected function newBaseQueryBuilder()
    {
        $connection = $this->getConnection();

        return new QueryBuilder($connection, $connection->getQueryGrammar(), $connection->getPostProcessor());
    }
}
