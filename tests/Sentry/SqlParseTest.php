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

    expect($result['operation'])->toBe('SELECT')
        ->and($result['tables'])->toBe('a');
});

test('select sql parse left join', function () {
    $query1 = 'select * from a left join b on a.id = b.id';
    $parser = new SqlParser();
    $result = $parser->parse($query1);

    expect($result['operation'])->toBe('SELECT')
        ->and($result['tables'])->toBe('a,b');
});

test('insert sql parse', function () {
    $query1 = 'insert into a (id) values (1)';
    $parser = new SqlParser();
    $result = $parser->parse($query1);

    expect($result['operation'])->toBe('INSERT')
        ->and($result['tables'])->toBe('a');
});

test('update sql parse', function () {
    $query1 = 'update a set id = 1';
    $parser = new SqlParser();
    $result = $parser->parse($query1);

    expect($result['operation'])->toBe('UPDATE')
        ->and($result['tables'])->toBe('a');
});
