<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ModelHashids\Concerns;

use Hyperf\Utils\Str;

trait HashidRouting
{
    /**
     * @see parent
     * @param mixed $query
     * @param mixed $value
     * @param null|mixed $field
     */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        $field = $field ?? $this->getRouteKeyName();

        if (
            $field && $field !== 'hashid'
            // Check for qualified columns
            && Str::afterLast($field, '.') !== 'hashid'
            // Avoid risking breaking backward compatibility by modifying
            // the getRouteKeyName() to return 'hashid' instead of null
            && Str::afterLast($field, '.') !== ''
        ) {
            return parent::resolveRouteBindingQuery($query, $value, $field);
        }

        return $query->byHashid($value);
    }

    /**
     * @see parent
     */
    public function getRouteKey()
    {
        return $this->hashid();
    }

    /**
     * @see parent
     */
    public function getRouteKeyName()
    {
        return null;
    }
}
