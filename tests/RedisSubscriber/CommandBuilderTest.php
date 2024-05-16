<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Redis\Subscriber\CommandBuilder;

test('test CommandBuilder', function () {
    $this->assertEquals(CommandBuilder::build(null), "$-1\r\n");
    $this->assertEquals(CommandBuilder::build(1), ":1\r\n");
    $this->assertEquals(CommandBuilder::build('foo'), "$3\r\nfoo\r\n");
    $this->assertEquals(CommandBuilder::build(['foo', 'bar']), "*2\r\n$3\r\nfoo\r\n$3\r\nbar\r\n");
    $this->assertEquals(CommandBuilder::build([1, [2, '4'], 2, 'bar']), "*4\r\n:1\r\n*2\r\n:2\r\n$1\r\n4\r\n:2\r\n$3\r\nbar\r\n");
});
