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

use FriendsOfHyperf\ValidatedDTO\Attributes\Cast;
use FriendsOfHyperf\ValidatedDTO\Attributes\Rules;
use FriendsOfHyperf\ValidatedDTO\Concerns\EmptyCasts;
use FriendsOfHyperf\ValidatedDTO\Concerns\EmptyDefaults;
use FriendsOfHyperf\ValidatedDTO\Concerns\EmptyRules;
use FriendsOfHyperf\ValidatedDTO\ValidatedDTO;
use Hyperf\Contract\Arrayable;

class ArrayableObjectDTO extends ValidatedDTO
{
    use EmptyCasts;
    use EmptyDefaults;
    use EmptyRules;

    #[Rules(['required'])]
    #[Cast(type: ArrayableObjectCast::class)]
    public Arrayable $object;
}
