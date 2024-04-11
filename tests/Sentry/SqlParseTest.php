<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Sentry;

use FriendsOfHyperf\Sentry\Util\SqlParser;

test('select sql parse simple', function () {
    $query1 = 'select * from a';
    $parser = new SqlParser();
    $result = $parser->parse($query1);

    expect($result['operate'])->toBe('SELECT')
        ->and($result['table'])->toBe(['a']);
});

test('select sql parse left join', function () {
    $query1 = 'select * from a left join b on a.id = b.id';
    $parser = new SqlParser();
    $result = $parser->parse($query1);

    expect($result['operate'])->toBe('SELECT')
        ->and($result['table'])->toBe(['a', 'b']);
});
