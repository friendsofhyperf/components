<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Helpers;

(function () {
    $files = [
        __DIR__ . '/Amqp.php',
        __DIR__ . '/AsyncQueue.php',
        __DIR__ . '/AsyncTask.php',
        __DIR__ . '/Command.php',
        __DIR__ . '/Functions.php',
        __DIR__ . '/Kafka.php',
    ];

    foreach ($files as $file) {
        require_once $file;
    }
})();
