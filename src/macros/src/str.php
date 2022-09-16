<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Macros\Foundation\UuidContainer;
use Hyperf\Utils\Str;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\InlinesOnly\InlinesOnlyExtension;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\MarkdownConverter;
use Ramsey\Uuid\Codec\TimestampFirstCombCodec;
use Ramsey\Uuid\Generator\CombGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use Symfony\Component\Uid\Ulid;
use voku\helper\ASCII;

if (! Str::hasMacro('betweenFirst')) {
    Str::macro('betweenFirst', function ($subject, $from, $to) {
        if ($from === '' || $to === '') {
            return $subject;
        }

        return Str::before(Str::after($subject, $from), $to);
    });
}

if (! Str::hasMacro('classNamespace')) {
    Str::macro('classNamespace', function ($value) {
        if ($pos = strrpos($value, '\\')) {
            return substr($value, 0, $pos);
        }

        return '';
    });
}

if (! Str::hasMacro('excerpt')) {
    Str::macro('excerpt', function ($text, $phrase = '', $options = []) {
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
    });
}

if (! Str::hasMacro('flushCache')) {
    Str::macro('flushCache', function () {
        static::$snakeCache = [];
        static::$camelCache = [];
        static::$studlyCache = [];
    });
}

if (! Str::hasMacro('headline')) {
    Str::macro('headline', function ($value) {
        $parts = explode(' ', $value);

        $parts = count($parts) > 1
            ? $parts = array_map([Str::class, 'title'], $parts)
            : $parts = array_map([Str::class, 'title'], Str::ucsplit(implode('_', $parts)));

        $collapsed = Str::replace(['-', '_', ' '], '_', implode('_', $parts));

        return implode(' ', array_filter(explode('_', $collapsed)));
    });
}

if (! Str::hasMacro('isAscii')) {
    Str::macro('isAscii', function ($value) {
        return ASCII::is_ascii((string) $value);
    });
}

if (! Str::hasMacro('isJson')) {
    Str::macro('isJson', function ($value) {
        if (! is_string($value)) {
            return false;
        }

        try {
            json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return false;
        }

        return true;
    });
}

if (! Str::hasMacro('isUuid')) {
    Str::macro('isUuid', function ($value) {
        if (! is_string($value)) {
            return false;
        }

        return preg_match('/^[\da-f]{8}-[\da-f]{4}-[\da-f]{4}-[\da-f]{4}-[\da-f]{12}$/iD', $value) > 0;
    });
}

if (! Str::hasMacro('isUlid')) {
    Str::macro('isUlid', function ($value) {
        if (! is_string($value)) {
            return false;
        }

        return Ulid::isValid($value);
    });
}

if (! Str::hasMacro('lcfirst')) {
    Str::macro('lcfirst', function ($string) {
        return Str::lower(Str::substr($string, 0, 1)) . Str::substr($string, 1);
    });
}

if (! Str::hasMacro('markdown')) {
    Str::macro('markdown', function ($string, array $options = []) {
        $converter = new GithubFlavoredMarkdownConverter($options);

        return (string) $converter->convert($string);
    });
}

if (! Str::hasMacro('inlineMarkdown')) {
    Str::macro('inlineMarkdown', function ($string, array $options = []) {
        $environment = new Environment($options);

        $environment->addExtension(new GithubFlavoredMarkdownExtension());
        $environment->addExtension(new InlinesOnlyExtension());

        $converter = new MarkdownConverter($environment);

        return (string) $converter->convert($string);
    });
}

if (! Str::hasMacro('orderedUuid')) {
    Str::macro('orderedUuid', function () {
        if (UuidContainer::$uuidFactory) {
            return call_user_func(UuidContainer::$uuidFactory);
        }

        $factory = new UuidFactory();

        $factory->setRandomGenerator(new CombGenerator(
            $factory->getRandomGenerator(),
            $factory->getNumberConverter()
        ));

        $factory->setCodec(new TimestampFirstCombCodec(
            $factory->getUuidBuilder()
        ));

        return $factory->uuid4();
    });
}

if (! Str::hasMacro('reverse')) {
    Str::macro('reverse', function ($value) {
        return implode(array_reverse(mb_str_split($value)));
    });
}

if (! Str::hasMacro('squish')) {
    Str::macro('squish', function ($value) {
        return preg_replace('~(\s|\x{3164})+~u', ' ', preg_replace('~^[\s﻿]+|[\s﻿]+$~u', '', $value));
    });
}

if (! Str::hasMacro('substrReplace')) {
    Str::macro('substrReplace', function ($string, $replace, $offset = 0, $length = null) {
        if ($length === null) {
            $length = strlen($string);
        }

        return substr_replace($string, $replace, $offset, $length);
    });
}

if (! Str::hasMacro('swap')) {
    Str::macro('swap', function (array $map, $subject) {
        return str_replace(array_keys($map), array_values($map), $subject);
    });
}

if (! Str::hasMacro('transliterate')) {
    Str::macro('transliterate', function ($string, $unknown = '?', $strict = false) {
        return ASCII::to_transliterate($string, $unknown, $strict);
    });
}

if (! Str::hasMacro('createUuidsUsing')) {
    Str::macro('createUuidsUsing', function (callable $factory = null) {
        UuidContainer::$uuidFactory = $factory;
    });
}

if (! Str::hasMacro('createUuidsNormally')) {
    Str::macro('createUuidsNormally', function () {
        UuidContainer::$uuidFactory = null;
    });
}

if (! Str::hasMacro('ucsplit')) {
    Str::macro('ucsplit', function ($string) {
        return preg_split('/(?=\p{Lu})/u', $string, -1, PREG_SPLIT_NO_EMPTY);
    });
}

if (! Str::hasMacro('ulid')) {
    Str::macro('ulid', function () {
        return new Ulid();
    });
}

if (! Str::hasMacro('uuid')) {
    Str::macro('uuid', function () {
        return UuidContainer::$uuidFactory
                    ? call_user_func(UuidContainer::$uuidFactory)
                    : Uuid::uuid4();
    });
}

if (! Str::hasMacro('wordCount')) {
    Str::macro('wordCount', function ($string) {
        return str_word_count($string);
    });
}
