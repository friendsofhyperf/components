<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
test('isLocal', function ($string, $method) {
    $env = new \FriendsOfHyperf\Support\Environment($string);
    expect($env->{$method}())->toBeTrue();
})->with([
    ['local', 'isLocal'],
    ['dev', 'isDev'],
    ['develop', 'isDevelop'],
    ['production', 'isProduction'],
    ['online', 'isOnline'],
]);
