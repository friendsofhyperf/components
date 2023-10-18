<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\ValidatedDTO\Datasets;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use FriendsOfHyperf\ValidatedDTO\Attributes\Cast;
use FriendsOfHyperf\ValidatedDTO\Attributes\DefaultValue;
use FriendsOfHyperf\ValidatedDTO\Attributes\Rules;
use FriendsOfHyperf\ValidatedDTO\Casting\CarbonCast;
use FriendsOfHyperf\ValidatedDTO\Casting\CarbonImmutableCast;
use FriendsOfHyperf\ValidatedDTO\Casting\EnumCast;
use FriendsOfHyperf\ValidatedDTO\Concerns\EmptyCasts;
use FriendsOfHyperf\ValidatedDTO\Concerns\EmptyDefaults;
use FriendsOfHyperf\ValidatedDTO\Concerns\EmptyRules;
use FriendsOfHyperf\ValidatedDTO\ValidatedDTO;

class ValidatedEnumDTO extends ValidatedDTO
{
    use EmptyCasts;
    use EmptyDefaults;
    use EmptyRules;

    #[Rules(['sometimes', 'string'])]
    #[DefaultValue('ONE')]
    #[Cast(EnumCast::class, DummyEnum::class)]
    public DummyEnum $unitEnum;

    #[Rules(['sometimes', 'string'])]
    #[DefaultValue('bar')]
    #[Cast(EnumCast::class, DummyBackedEnum::class)]
    public DummyBackedEnum $backedEnum;

    #[Rules(['sometimes', 'string'])]
    #[DefaultValue('2023-10-16')]
    #[Cast(CarbonCast::class)]
    public Carbon $carbon;

    #[Rules(['sometimes', 'string'])]
    #[DefaultValue('2023-10-15')]
    #[Cast(CarbonImmutableCast::class)]
    public CarbonImmutable $carbonImmutable;
}
