<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ModelHashids\Concerns;

use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Scope;

class HashidScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
    }

    public function extend(Builder $builder)
    {
        $builder->macro('findByHashid', function (Builder $builder, $hashid) {
            return $builder->byHashid($hashid)->first();
        });

        $builder->macro('findByHashidOrFail', function (Builder $builder, $hashid) {
            return $builder->byHashid($hashid)->firstOrFail();
        });

        $builder->macro('byHashid', function (Builder $builder, $hashid) {
            $model = $builder->getModel();

            return $builder->where(
                $model->qualifyColumn($model->getKeyName()),
                $model->hashidToId($hashid)
            );
        });
    }
}
