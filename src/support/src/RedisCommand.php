<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Support;

use Stringable;

use function Hyperf\Collection\collect;

class RedisCommand implements Stringable
{
    private ?string $formatted = null;

    public function __construct(public string $command, public array $parameters = [])
    {
    }

    public function __toString(): string
    {
        return $this->formatted ??= $this->formatCommand($this->command, $this->parameters);
    }

    protected function formatCommand(string $command, array $parameters): string
    {
        $parameters = collect($parameters)->map(function ($parameter) {
            if (is_array($parameter)) {
                return collect($parameter)->map(function ($value, $key) {
                    if (is_array($value)) {
                        return sprintf('%s %s', $key, json_encode($value));
                    }

                    return is_int($key) ? $value : sprintf('%s %s', $key, $value);
                })->implode(' ');
            }

            return $parameter;
        })->implode(' ');

        return sprintf('%s %s', $command, $parameters);
    }
}
