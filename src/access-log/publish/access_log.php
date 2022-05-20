<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/1.x/README.md
 * @contact  huangdijia@gmail.com
 */
return [
    'enable' => env('ACCESS_LOG_ENABLE', false),
    'logger' => [
        'group' => 'default',
        'time_format' => 'd/M/Y:H:i:s O',
    ],
    'ignore_user_agents' => [
        'Consul Health Check',
    ],
    'ignore_paths' => [
        '/favicon.ico',
    ],
];
