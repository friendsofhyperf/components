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

// Str::createUuidsNormally() tests
assertType('void', Str::createUuidsNormally());

// Str::createUuidsUsing() tests
assertType('void', Str::createUuidsUsing(null));
assertType('void', Str::createUuidsUsing(fn () => 'custom-uuid'));

// Str::deduplicate() tests
assertType('string', Str::deduplicate('hello  world'));
assertType('string', Str::deduplicate('a--b--c', '-'));

// Str::inlineMarkdown() tests
assertType('string', Str::inlineMarkdown('**bold**'));
assertType('string', Str::inlineMarkdown('*italic*', []));

// Str::markdown() tests
assertType('string', Str::markdown('# Heading'));
assertType('string', Str::markdown('## Title', [], []));

// Str::transliterate() tests
assertType('string', Str::transliterate('こんにちは'));
assertType('string', Str::transliterate('café', '?', false));

// Str::doesntEndWith() tests
assertType('bool', Str::doesntEndWith('hello world', 'world'));
assertType('bool', Str::doesntEndWith('test', ['ing', 'ed']));

// Str::doesntStartWith() tests
assertType('bool', Str::doesntStartWith('hello world', 'hello'));
assertType('bool', Str::doesntStartWith('test', ['pre', 'post']));
