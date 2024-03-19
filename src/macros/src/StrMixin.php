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

use FriendsOfHyperf\Support\UuidContainer;
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
    public function apa()
    {
        return function ($value) {
            if (trim($value) === '') {
                return $value;
            }

            $minorWords = [
                'and', 'as', 'but', 'for', 'if', 'nor', 'or', 'so', 'yet', 'a', 'an',
                'the', 'at', 'by', 'for', 'in', 'of', 'off', 'on', 'per', 'to', 'up', 'via',
            ];

            $endPunctuation = ['.', '!', '?', ':', '—', ','];

            $words = preg_split('/\s+/', $value, -1, PREG_SPLIT_NO_EMPTY);

            $words[0] = ucfirst(mb_strtolower($words[0]));

            for ($i = 0; $i < count($words); ++$i) {
                $lowercaseWord = mb_strtolower($words[$i]);

                if (str_contains($lowercaseWord, '-')) {
                    $hyphenatedWords = explode('-', $lowercaseWord);

                    $hyphenatedWords = array_map(function ($part) use ($minorWords) {
                        return (in_array($part, $minorWords) && mb_strlen($part) <= 3) ? $part : ucfirst($part);
                    }, $hyphenatedWords);

                    $words[$i] = implode('-', $hyphenatedWords);
                } else {
                    if (in_array($lowercaseWord, $minorWords)
                        && mb_strlen($lowercaseWord) <= 3
                        && ! ($i === 0 || in_array(mb_substr($words[$i - 1], -1), $endPunctuation))) {
                        $words[$i] = $lowercaseWord;
                    } else {
                        $words[$i] = ucfirst($lowercaseWord);
                    }
                }
            }

            return implode(' ', $words);
        };
    }

    public function createUuidsNormally()
    {
        return fn () => UuidContainer::$uuidFactory = null;
    }

    public function createUuidsUsing()
    {
        return fn (?callable $factory = null) => UuidContainer::$uuidFactory = $factory;
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
