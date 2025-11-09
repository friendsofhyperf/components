<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Mail;

use FriendsOfHyperf\Mail\Markdown;
use Hyperf\ViewEngine\Contract\FactoryInterface as Factory;
use Hyperf\ViewEngine\View;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\Group('mail')]
class MailMarkdownTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testRenderFunctionReturnsHtml()
    {
        $viewFactory = m::mock(Factory::class);

        $viewInterface = m::mock(View::class);
        $viewInterface->allows('render')->andReturns('<html></html>', 'body {}');
        $viewFactory->allows('make')->andReturn($viewInterface);
        $markdown = new Markdown($viewFactory);
        $viewFactory->shouldReceive('replaceNamespace')->once()->with('mail', $markdown->htmlComponentPaths())->andReturnSelf();
        $viewFactory->shouldReceive('exists')->with('mail.default')->andReturn(false);

        $result = $markdown->render('view', []);

        $this->assertStringContainsString('<html></html>', $result->toHtml());
    }

    public function testRenderFunctionReturnsHtmlWithCustomTheme()
    {
        $viewFactory = m::mock(Factory::class);

        $viewInterface = m::mock(View::class);

        $viewInterface->allows('render')->andReturn('<html></html>', 'body {}');
        $viewFactory->allows('make')->andReturn($viewInterface);
        $markdown = new Markdown($viewFactory);
        $markdown->theme('yaz');
        $viewFactory->shouldReceive('replaceNamespace')->once()->with('mail', $markdown->htmlComponentPaths())->andReturnSelf();
        $viewFactory->shouldReceive('exists')->with('mail.yaz')->andReturn(true);
        $result = $markdown->render('view', []);

        $this->assertStringContainsString('<html></html>', $result->toHtml());
    }

    public function testRenderFunctionReturnsHtmlWithCustomThemeWithMailPrefix()
    {
        $viewFactory = m::mock(Factory::class);

        $viewInterface = m::mock(View::class);
        $viewInterface->allows('render')->twice()->andReturns('<html></html>', 'body {}');
        $viewFactory->allows('make')->andReturn($viewInterface);
        $markdown = new Markdown($viewFactory);
        $markdown->theme('mail.yaz');
        $viewFactory->shouldReceive('replaceNamespace')->once()->with('mail', $markdown->htmlComponentPaths())->andReturnSelf();
        $viewFactory->shouldReceive('exists')->with('mail.yaz')->andReturn(true);

        $result = $markdown->render('view', []);

        $this->assertStringContainsString('<html></html>', $result->toHtml());
    }

    public function testRenderTextReturnsText()
    {
        $viewFactory = m::mock(Factory::class);

        $viewInterface = m::mock(View::class);
        $viewInterface->allows('render')->andReturn('text');
        $viewFactory->allows('make')->andReturn($viewInterface);
        $markdown = new Markdown($viewFactory);
        $viewFactory->shouldReceive('replaceNamespace')->once()->with('mail', $markdown->textComponentPaths())->andReturnSelf();
        $result = $markdown->renderText('view', [])->toHtml();

        $this->assertSame('text', $result);
    }

    public function testParseReturnsParsedMarkdown()
    {
        $viewFactory = m::mock(Factory::class);
        $viewInterface = m::mock(View::class);
        $viewInterface->allows('render');
        $viewFactory->allows('make')->andReturn($viewInterface);
        $markdown = new Markdown($viewFactory);

        $result = $markdown->parse('# Something')->toHtml();

        $this->assertSame("<h1>Something</h1>\n", $result);
    }
}
