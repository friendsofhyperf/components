<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use Hyperf\Stringable\Str;

use function PHPStan\Testing\assertType;

// Stringable::deduplicate() tests
assertType('Hyperf\Stringable\Stringable', Str::of('hello  world')->deduplicate());
assertType('Hyperf\Stringable\Stringable', Str::of('a--b')->deduplicate('-'));

// Stringable::hash() tests
assertType('Hyperf\Stringable\Stringable', Str::of('password')->hash('sha256'));
assertType('Hyperf\Stringable\Stringable', Str::of('text')->hash('md5'));

// Stringable::inlineMarkdown() tests
assertType('Hyperf\Stringable\Stringable', Str::of('**bold**')->inlineMarkdown());
assertType('Hyperf\Stringable\Stringable', Str::of('*italic*')->inlineMarkdown([]));

// Stringable::markdown() tests
assertType('Hyperf\Stringable\Stringable', Str::of('# Heading')->markdown());
assertType('Hyperf\Stringable\Stringable', Str::of('## Title')->markdown([], []));

// Stringable::toHtmlString() tests
assertType('FriendsOfHyperf\Support\HtmlString', Str::of('<p>test</p>')->toHtmlString());

// Stringable::whenIsAscii() tests
assertType('Hyperf\Stringable\Stringable', Str::of('hello')->whenIsAscii(fn ($s) => $s->upper()));
assertType('Hyperf\Stringable\Stringable', Str::of('test')->whenIsAscii(fn ($s) => $s->upper(), fn ($s) => $s->lower()));

// Stringable::doesntEndWith() tests
assertType('bool', Str::of('hello world')->doesntEndWith('world'));
assertType('bool', Str::of('test')->doesntEndWith(['ing', 'ed']));

// Stringable::doesntStartWith() tests
assertType('bool', Str::of('hello world')->doesntStartWith('hello'));
assertType('bool', Str::of('test')->doesntStartWith(['pre', 'post']));
