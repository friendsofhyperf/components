<?php


declare(strict_types=1);

/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

use function Hyperf\Support\env;

return [
    'dsn' => env('MAIL_DSN', 'null://localhost'),
    'from' => env('MAIL_FROM', 'from@example.com'),
    'envelope' => null,
];
