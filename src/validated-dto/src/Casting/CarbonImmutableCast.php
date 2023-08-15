<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ValidatedDTO\Casting;

use Carbon\CarbonImmutable;
use FriendsOfHyperf\ValidatedDTO\Exception\CastException;
use Throwable;

class CarbonImmutableCast implements Castable
{
    public function __construct(
        private ?string $timezone = null,
        private ?string $format = null
    ) {
    }

    /**
     * @throws CastException
     */
    public function cast(string $property, mixed $value): CarbonImmutable
    {
        try {
            return is_null($this->format)
                ? CarbonImmutable::parse($value, $this->timezone)
                : CarbonImmutable::createFromFormat($this->format, $value, $this->timezone);
        } catch (Throwable) {
            throw new CastException($property);
        }
    }
}
