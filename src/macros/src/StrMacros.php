<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Macros;

use FriendsOfHyperf\Macros\Foundation\UuidContainer;
use Hyperf\Utils\Str;
use JsonException;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\InlinesOnly\InlinesOnlyExtension;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\MarkdownConverter;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Symfony\Component\Uid\Ulid as SymfonyUlid;
use voku\helper\ASCII;

/**
 * @mixin Str
 */
class StrMacros
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
                (function ($startWithRadius) use ($startStr) { return $startWithRadius->exactly($startStr); })($start),
                function ($startWithRadius) use ($omission) { return $startWithRadius->prepend($omission); },
            );

            $endStr = rtrim($matches[3]);
            $end = Str::of(mb_substr($endStr, 0, $radius, 'UTF-8'))->rtrim();
            $end = $end->unless(
                (function ($endWithRadius) use ($endStr) { return $endWithRadius->exactly($endStr); })($end),
                function ($endWithRadius) use ($omission) { return $endWithRadius->append($omission); },
            );

            return $start->append($matches[2], $end)->__toString();
        };
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

    public function isUlid()
    {
        return function ($value) {
            if (! is_string($value)) {
                return false;
            }

            if (\strlen($value) !== 26) {
                return false;
            }

            if (strspn($value, '0123456789ABCDEFGHJKMNPQRSTVWXYZabcdefghjkmnpqrstvwxyz') !== 26) {
                return false;
            }

            return $value[0] <= '7';
        };
    }

    public function isUuid()
    {
        return function ($value) {
            if (! is_string($value)) {
                return false;
            }

            return preg_match('/^[\da-f]{8}-[\da-f]{4}-[\da-f]{4}-[\da-f]{4}-[\da-f]{12}$/iD', $value) > 0;
        };
    }

    public function lcfirst()
    {
        return fn ($string) => Str::lower(Str::substr($string, 0, 1)) . Str::substr($string, 1);
    }

    public function markdown()
    {
        return function ($string, array $options = []) {
            $converter = new GithubFlavoredMarkdownConverter($options);

            return (string) $converter->convert($string);
        };
    }

    public function orderedUuid()
    {
        return function () {
            return Str::uuid();
        };
    }

    public function reverse()
    {
        return fn ($value) => implode(array_reverse(mb_str_split($value)));
    }

    public function squish()
    {
        return fn ($value) => preg_replace('~(\s|\x{3164})+~u', ' ', preg_replace('~^[\s﻿]+|[\s﻿]+$~u', '', $value));
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

    public function ulid()
    {
        return fn () => new SymfonyUlid();
    }

    public function uuid()
    {
        return fn () => UuidContainer::$uuidFactory
            ? call_user_func(UuidContainer::$uuidFactory)
            : RamseyUuid::uuid7();
    }

    public function wordCount()
    {
        return fn ($string) => str_word_count($string);
    }

    public function wrap()
    {
        return fn ($value, $before, $after = null) => $before . $value . ($after ??= $before);
    }
}
