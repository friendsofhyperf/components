<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Mail;

use FriendsOfHyperf\Support\HtmlString;
use Hyperf\Stringable\Str;
use Hyperf\ViewEngine\Contract\FactoryInterface;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class Markdown
{
    /**
     * The view factory implementation.
     */
    protected FactoryInterface $view;

    /**
     * The current theme being used when generating emails.
     */
    protected string $theme = 'default';

    /**
     * The registered component paths.
     */
    protected array $componentPaths = [];

    /**
     * Create a new Markdown renderer instance.
     */
    public function __construct(FactoryInterface $view, array $options = [])
    {
        $this->view = $view;
        $this->theme = $options['theme'] ?? 'default';
        $this->loadComponentsFrom($options['paths'] ?? []);
    }

    /**
     * Render the Markdown template into HTML.
     */
    public function render(string $view, array $data = [], ?CssToInlineStyles $inliner = null): HtmlString
    {
        if (method_exists($this->view, 'flushFinderCache')) {
            $this->view->flushFinderCache();
        }
        $contents = $this->view->replaceNamespace(
            'mail',
            $this->htmlComponentPaths()
        )->make($view, $data)->render();
        if ($this->view->exists($customTheme = Str::start($this->theme, 'mail.'))) {
            $theme = $customTheme;
        } else {
            $theme = str_contains($this->theme, '::')
                ? $this->theme
                : 'mail::themes.' . $this->theme;
        }

        return new HtmlString(($inliner ?: new CssToInlineStyles())->convert(
            $contents,
            $this->view->make($theme, $data)->render()
        ));
    }

    /**
     * Render the Markdown template into text.
     */
    public function renderText(string $view, array $data = []): HtmlString
    {
        if (method_exists($this->view, 'flushFinderCache')) {
            $this->view->flushFinderCache();
        }

        $contents = $this->view->replaceNamespace(
            'mail',
            $this->textComponentPaths()
        )->make($view, $data)->render();

        $contents = is_array($contents) ? $contents[0] ?? '' : $contents;
        return new HtmlString(
            html_entity_decode(preg_replace("/[\r\n]{2,}/", "\n\n", $contents), ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Parse the given Markdown text into HTML.
     */
    public static function parse(string $text): HtmlString
    {
        $environment = new Environment([
            'allow_unsafe_links' => false,
        ]);

        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new TableExtension());

        $converter = new MarkdownConverter($environment);

        return new HtmlString($converter->convert($text)->getContent());
    }

    /**
     * Get the HTML component paths.
     */
    public function htmlComponentPaths(): array
    {
        return array_map(static function ($path) {
            return $path . '/html';
        }, $this->componentPaths());
    }

    /**
     * Get the text component paths.
     */
    public function textComponentPaths(): array
    {
        return array_map(static function ($path) {
            return $path . '/text';
        }, $this->componentPaths());
    }

    /**
     * Register new mail component paths.
     */
    public function loadComponentsFrom(array $paths = []): void
    {
        $this->componentPaths = $paths;
    }

    /**
     * Set the default theme to be used.
     */
    public function theme(string $theme): static
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * Get the theme currently being used by the renderer.
     */
    public function getTheme(): string
    {
        return $this->theme;
    }

    /**
     * Get the component paths.
     */
    protected function componentPaths(): array
    {
        return array_unique(array_merge($this->componentPaths, [
            dirname(__DIR__) . '/publish/resources/views',
        ]));
    }
}
