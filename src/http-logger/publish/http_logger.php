<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Http\Logger;

use function Hyperf\Support\env;

return [
    'log_profile' => Logger\Profile\DefaultLogProfile::class,

    'log_writer' => Logger\Writer\DefaultLogWriter::class,

    'log_group' => env('HTTP_LOGGER_LOG_GROUP', 'default'),

    'log_name' => env('HTTP_LOGGER_LOG_NAME', 'http'),

    'log_level' => env('HTTP_LOGGER_LOG_LEVEL', 'info'),

    'log_format' => "%host% %remote_addr% [%time_local%] \"%request%\" %status% %body_bytes_sent% \"%http_referer%\" \"%http_user_agent%\" \"%http_x_forwarded_for%\" %request_time% %upstream_response_time% %upstream_addr%\n",

    'log_time_format' => 'd/M/Y:H:i:s O',
];
