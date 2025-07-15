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

use FriendsOfHyperf\Support\HtmlString;
use Hyperf\Stringable\Str;
use Hyperf\Stringable\Stringable;
use RuntimeException;

use function FriendsOfHyperf\Encryption\decrypt;
use function FriendsOfHyperf\Encryption\encrypt;

/**
 * @mixin Stringable
 * @property string $value
 */
class StringableMixin
{
    public function encrypt()
    {
        if (! function_exists('FriendsOfHyperf\Encryption\encrypt')) {
            throw new RuntimeException('The "encrypt" function is not defined. Please ensure the "friendsofhyperf/encryption" component is installed and configured.');
        }

        /* @phpstan-ignore-next-line */
        return fn (bool $serialize = false) => new static(encrypt($this->value, $serialize));
    }

    public function decrypt()
    {
        if (! function_exists('FriendsOfHyperf\Encryption\decrypt')) {
            throw new RuntimeException('The "decrypt" function is not defined. Please ensure the "friendsofhyperf/encryption" component is installed and configured.');
        }

        /* @phpstan-ignore-next-line */
        return fn (bool $serialize = false) => new static(decrypt($this->value, $serialize));
    }

    public function deduplicate()
    {
        /* @phpstan-ignore-next-line */
        return fn (string $character = ' ') => new static(Str::deduplicate($this->value, $character));
    }

    public function hash()
    {
        /* @phpstan-ignore-next-line */
        return fn (string $algorithm) => new static(hash($algorithm, $this->value));
    }

    public function inlineMarkdown()
    {
        /* @phpstan-ignore-next-line */
        return fn (array $options = []) => new static(Str::inlineMarkdown($this->value, $options));
    }

    public function markdown()
    {
        /* @phpstan-ignore-next-line */
        return fn (array $options = [], array $extensions = []) => new static(Str::markdown($this->value, $options, $extensions));
    }

    public function toHtmlString()
    {
        /* @phpstan-ignore-next-line */
        return fn () => new HtmlString($this->value);
    }

    public function whenIsAscii()
    {
        /* @phpstan-ignore-next-line */
        return fn ($callback, $default = null) => $this->when($this->isAscii(), $callback, $default);
    }

    public function doesntEndWith()
    {
        /* @phpstan-ignore-next-line */
        return fn ($needles) => ! $this->endsWith($needles);
    }

    public function doesntStartWith()
    {
        /* @phpstan-ignore-next-line */
        return fn ($needles) => ! $this->startsWith($needles);
    }
}
