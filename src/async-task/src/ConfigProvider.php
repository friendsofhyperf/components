<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\AsyncTask;

class ConfigProvider
{
    public function __invoke()
    {
        defined('BASE_PATH') or define('BASE_PATH', dirname(__DIR__, 3));

        return [
            'listeners' => [
                Listener\BindServerAndWorkerIdListener::class,
                Listener\OnPipeMessageListener::class,
                Listener\TaskHandleListener::class,
            ],
        ];
    }
}
