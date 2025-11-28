<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Metrics\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\DbConnection\Pool\PoolFactory;

class DBPoolWatcher extends PoolWatcher
{
    public function getPrefix(): string
    {
        return 'mysql';
    }

    public function process(object $event): void
    {
        $config = $this->container->get(ConfigInterface::class);
        $poolNames = array_keys($config->get('databases', ['default' => []]));

        foreach ($poolNames as $poolName) {
            $workerId = (int) ($event->workerId ?? 0);
            $pool = $this
                ->container
                ->get(PoolFactory::class)
                ->getPool($poolName);
            $this->watch($pool, $poolName, $workerId);
        }
    }
}
