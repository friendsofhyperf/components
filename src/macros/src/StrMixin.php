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
use Hyperf\Collection\Collection;
use Hyperf\Stringable\Str;
use JsonException;
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
    public function betweenFirst()
    {
        return function ($subject, $from, $to) {
            if ($from === '' || $to === '') {
                return $subject;
            }

            return Str::before(Str::after($subject, $from), $to);
        };
    }

    public function classNamespace()
    {
        return function ($value) {
            if ($pos = strrpos($value, '\\')) {
                return substr($value, 0, $pos);
            }

            return '';
        };
    }

    public function createUuidsNormally()
    {
        return fn () => UuidContainer::$uuidFactory = null;
    }

    public function createUuidsUsing()
    {
        return fn (callable $factory = null) => UuidContainer::$uuidFactory = $factory;
    }

    public function excerpt()
    {
        return function ($text, $phrase = '', $options = []) {
            $radius = $options['radius'] ?? 100;
            $omission = $options['omission'] ?? '...';

            preg_match('/^(.*?)(' . preg_quote((string) $phrase) . ')(.*)$/iu', (string) $text, $matches);

            if (empty($matches)) {
                return null;
            }

            $startStr = ltrim($matches[1]);
            $start = Str::of(mb_substr($matches[1], max(mb_strlen($startStr, 'UTF-8') - $radius, 0), $radius, 'UTF-8'))->ltrim();
            $start = $start->unless(
                (fn ($startWithRadius) => $startWithRadius->exactly($startStr))($start),
                fn ($startWithRadius) => $startWithRadius->prepend($omission),
            );

            $endStr = rtrim($matches[3]);
            $end = Str::of(mb_substr($endStr, 0, $radius, 'UTF-8'))->rtrim();
            $end = $end->unless(
                (fn ($endWithRadius) => $endWithRadius->exactly($endStr))($end),
                fn ($endWithRadius) => $endWithRadius->append($omission),
            );

            return $start->append($matches[2], $end)->__toString();
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

    public function isJson()
    {
        return function ($value) {
            if (! is_string($value)) {
                return false;
            }

            try {
                json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                return false;
            }

            return true;
        };
    }

    public function lcfirst()
    {
        return fn ($string) => Str::lower(Str::substr($string, 0, 1)) . Str::substr($string, 1);
    }

    public function markdown()
    {
        return fn ($string, array $options = []) => (string) (new GithubFlavoredMarkdownConverter($options))->convert($string);
    }

    public function password()
    {
        return fn ($length = 32, $letters = true, $numbers = true, $symbols = true, $spaces = false) => (new Collection())
            ->when($letters, fn ($c) => $c->merge([
                'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k',
                'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v',
                'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G',
                'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R',
                'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            ]))
            ->when($numbers, fn ($c) => $c->merge([
                '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
            ]))
            ->when($symbols, fn ($c) => $c->merge([
                '~', '!', '#', '$', '%', '^', '&', '*', '(', ')', '-',
                '_', '.', ',', '<', '>', '?', '/', '\\', '{', '}', '[',
                ']', '|', ':', ';',
            ]))
            ->when($spaces, fn ($c) => $c->merge([' ']))
            ->pipe(fn ($c) => Collection::times($length, fn () => $c[random_int(0, $c->count() - 1)]))
            ->implode('');
    }

    public static function replaceStart()
    {
        return function ($search, $replace, $subject) {
            $search = (string) $search;

            if ($search === '') {
                return $subject;
            }

            if (static::startsWith($subject, $search)) {
                return static::replaceFirst($search, $replace, $subject);
            }

            return $subject;
        };
    }

    public static function replaceEnd()
    {
        return function ($search, $replace, $subject) {
            $search = (string) $search;

            if ($search === '') {
                return $subject;
            }

            if (static::endsWith($subject, $search)) {
                return static::replaceLast($search, $replace, $subject);
            }

            return $subject;
        };
    }

    public function reverse()
    {
        return fn ($value) => implode(array_reverse(mb_str_split($value)));
    }

    public function squish()
    {
        return fn ($value) => preg_replace('~(\s|\x{3164}|\x{1160})+~u', ' ', preg_replace('~^[\s\x{FEFF}]+|[\s\x{FEFF}]+$~u', '', $value));
    }

    public function substrReplace()
    {
        return function ($string, $replace, $offset = 0, $length = null) {
            if ($length === null) {
                $length = strlen($string);
            }

            return substr_replace($string, $replace, $offset, $length);
        };
    }

    public function swap()
    {
        return fn (array $map, $subject) => str_replace(array_keys($map), array_values($map), $subject);
    }

    public function transliterate()
    {
        return fn ($string, $unknown = '?', $strict = false) => ASCII::to_transliterate($string, $unknown, $strict);
    }

    public function ucsplit()
    {
        return fn ($string) => preg_split('/(?=\p{Lu})/u', $string, -1, PREG_SPLIT_NO_EMPTY);
    }

    public function wordCount()
    {
        return fn ($string) => str_word_count($string);
    }

    public function wrap()
    {
        return fn ($value, $before, $after = null) => $before . $value . ($after ??= $before);
    }

    public static function wordWrap()
    {
        return fn ($string, $characters = 75, $break = "\n", $cutLongWords = false) => wordwrap($string, $characters, $break, $cutLongWords);
    }
}
