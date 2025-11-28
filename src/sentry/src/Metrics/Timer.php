<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Metrics;

use Sentry\Unit;

use function FriendsOfHyperf\Sentry\metrics;

class Timer
{
    public float $startAt;

    protected bool $ended = false;

    public function __construct(
        protected string $name,
        protected array $attributes = [],
        protected ?Unit $unit = null,
    ) {
        $this->startAt = microtime(true);
    }

    public function __destruct()
    {
        $this->end();
    }

    public static function make(
        string $name,
        array $attributes = [],
        ?Unit $unit = null,
    ): static {
        return new static($name, $attributes, $unit);
    }

    public function end(bool $flush = false): void
    {
        if ($this->ended) {
            return;
        }

        metrics()->distribution(
            $this->name,
            (int) ((microtime(true) - $this->startAt) * 1000),
            $this->attributes,
            $this->unit ?? Unit::second()
        );

        $flush && metrics()->flush();

        $this->ended = true;
    }
}
