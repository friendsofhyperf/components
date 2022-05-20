<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/1.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Tests\Tinker;

use FriendsOfHyperf\Tests\TestCase;
use FriendsOfHyperf\Tinker\TinkerCaster;
use Hyperf\Utils\Collection;
use Hyperf\Utils\Stringable;
use Hyperf\ViewEngine\HtmlString;

/**
 * @internal
 * @coversNothing
 */
class TinkerCasterTest extends TestCase
{
    public function testCanCastCollection()
    {
        $caster = new TinkerCaster();

        $result = $caster->castCollection(new Collection(['foo', 'bar']));

        $this->assertSame([['foo', 'bar']], array_values($result));
    }

    public function testCanCastHtmlString()
    {
        $this->markTestSkipped('Skipped test');

        $caster = new TinkerCaster();

        $result = $caster->castHtmlString(new HtmlString('<p>foo</p>'));

        $this->assertSame(['<p>foo</p>'], array_values($result));
    }

    public function testCanCastStringable()
    {
        $caster = new TinkerCaster();

        $result = $caster->castStringable(new Stringable('test string'));

        $this->assertSame(['test string'], array_values($result));
    }
}
