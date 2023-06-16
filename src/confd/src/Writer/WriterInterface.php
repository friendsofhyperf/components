<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Confd\Writer;

interface WriterInterface
{
    public function set(string $key, string $value, bool $forceQuote = false): self;

    public function setValues(array $values, bool $forceQuote = false): self;

    public function write(bool $force = false): bool;
}
