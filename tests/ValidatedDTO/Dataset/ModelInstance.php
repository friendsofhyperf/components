<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace Tests\ValidatedDTO\Dataset;

use Hyperf\Database\Model\Model;

class ModelInstance extends Model
{
    protected array $fillable = ['name', 'age'];
}
