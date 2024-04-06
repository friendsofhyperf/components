<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Macros;

use FriendsOfHyperf\Macros\Foundation\UuidContainer;
use Hyperf\Stringable\Str;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\InlinesOnly\InlinesOnlyExtension;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\MarkdownConverter;
use voku\helper\ASCII;

/**
 * @mixin Str
 */
class StrMixin
{
    public function createUuidsNormally()
    {
        return fn () => UuidContainer::$uuidFactory = null;
    }

    public function createUuidsUsing()
    {
        return fn (?callable $factory = null) => UuidContainer::$uuidFactory = $factory;
    }

    public function flushCache()
    {
        return function () {
            /* @phpstan-ignore-next-line */
            static::$snakeCache = [];
            /* @phpstan-ignore-next-line */
            static::$camelCache = [];
            /* @phpstan-ignore-next-line */
            static::$studlyCache = [];
        };
    }

    public function headline()
    {
        return function ($value) {
            $parts = explode(' ', $value);

            $parts = count($parts) > 1
                ? $parts = array_map([Str::class, 'title'], $parts)
                : $parts = array_map([Str::class, 'title'], Str::ucsplit(implode('_', $parts)));

            $collapsed = Str::replace(['-', '_', ' '], '_', implode('_', $parts));

            return implode(' ', array_filter(explode('_', $collapsed)));
        };
    }

    public function inlineMarkdown()
    {
        return function ($string, array $options = []) {
            $environment = new Environment($options);

            $environment->addExtension(new GithubFlavoredMarkdownExtension());
            $environment->addExtension(new InlinesOnlyExtension());

            $converter = new MarkdownConverter($environment);

            return (string) $converter->convert($string);
        };
    }

    public function isAscii()
    {
        return fn ($value) => ASCII::is_ascii((string) $value);
    }

    public function markdown()
    {
        return fn ($string, array $options = []) => (string) (new GithubFlavoredMarkdownConverter($options))->convert($string);
    }

    public static function position()
    {
        return fn ($haystack, $needle, $offset = 0, $encoding = null) => mb_strpos($haystack, (string) $needle, $offset, $encoding);
    }

    public static function take()
    {
        return function ($string, int $limit) {
            if ($limit < 0) {
                return static::substr($string, $limit);
            }

            return static::substr($string, 0, $limit);
        };
    }

    public function transliterate()
    {
        return fn ($string, $unknown = '?', $strict = false) => ASCII::to_transliterate($string, $unknown, $strict);
    }
}
