<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use Hyperf\Database\Model\Model;

class Category extends Model
{
    use Hyperf\Database\Model\SoftDeletes;
    use FriendsOfHyperf\Nestedset\NodeTrait;

    public bool $timestamps = false;

    protected array $fillable = ['name', 'parent_id'];

    public static function resetActionsPerformed(): void
    {
        static::$actionsPerformed = 0;
    }
}
