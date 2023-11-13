<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope\Model;

use function Hyperf\Config\config;

abstract class Model extends \Hyperf\DbConnection\Model\Model
{
    public function getConnectionName()
    {
        return config('telescope.database.connection', 'default');
    }
}
