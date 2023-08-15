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

use Carbon\Carbon;
use FriendsOfHyperf\ValidatedDTO\Exception\CastException;
use Throwable;

class CarbonCast implements Castable
{
    public function __construct(
        private ?string $timezone = null,
        private ?string $format = null
    ) {
    }

    /**
     * @throws CastException
     */
    public function cast(string $property, mixed $value): Carbon
    {
        try {
            return is_null($this->format)
                ? Carbon::parse($value, $this->timezone)
                : Carbon::createFromFormat($this->format, $value, $this->timezone);
        } catch (Throwable) {
            throw new CastException($property);
        }
    }
}
