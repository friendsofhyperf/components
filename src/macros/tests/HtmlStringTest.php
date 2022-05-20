<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/macros.
 *
 * @link     https://github.com/friendsofhyperf/macros
 * @document https://github.com/friendsofhyperf/macros/blob/1.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Macros\Tests;

use FriendsOfHyperf\Macros\Foundation\HtmlString;

/**
 * @internal
 * @coversNothing
 */
class HtmlStringTest extends TestCase
{
    public function testToHtml()
    {
        $str = '<h1>foo</h1>';
        $html = new HtmlString('<h1>foo</h1>');
        $this->assertEquals($str, $html->toHtml());
    }

    public function testToString()
    {
        $str = '<h1>foo</h1>';
        $html = new HtmlString('<h1>foo</h1>');
        $this->assertEquals($str, (string) $html);
    }

    public function testIsEmpty()
    {
        $this->assertTrue((new HtmlString(''))->isEmpty());
    }

    public function testIsNotEmpty()
    {
        $this->assertTrue((new HtmlString('foo'))->isNotEmpty());
    }
}
