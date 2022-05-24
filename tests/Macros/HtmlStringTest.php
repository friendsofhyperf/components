<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Tests\Macros;

use FriendsOfHyperf\Macros\Foundation\HtmlString;
use FriendsOfHyperf\Tests\TestCase;

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
