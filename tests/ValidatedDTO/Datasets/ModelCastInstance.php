<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\ValidatedDTO\Datasets;

use Hyperf\Database\Model\Model;

class ModelCastInstance extends Model
{
    protected array $fillable = ['name', 'metadata'];

    protected array $casts = [
        'metadata' => AttributesDTO::class,
    ];
}
