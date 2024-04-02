<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
class DuplicateCategory extends Hyperf\Database\Model\Model
{
    use FriendsOfHyperf\Nestedset\NodeTrait;

    public bool $timestamps = false;

    protected ?string $table = 'categories';

    protected array $fillable = ['name'];
}
