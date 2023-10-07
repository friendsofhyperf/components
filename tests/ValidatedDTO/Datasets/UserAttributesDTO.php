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

use FriendsOfHyperf\ValidatedDTO\Attributes\DefaultValue;
use FriendsOfHyperf\ValidatedDTO\Attributes\Map;
use FriendsOfHyperf\ValidatedDTO\Attributes\Rules;
use FriendsOfHyperf\ValidatedDTO\Concerns\EmptyCasts;
use FriendsOfHyperf\ValidatedDTO\Concerns\EmptyDefaults;
use FriendsOfHyperf\ValidatedDTO\Concerns\EmptyRules;
use FriendsOfHyperf\ValidatedDTO\ValidatedDTO;

class UserAttributesDTO extends ValidatedDTO
{
    use EmptyCasts;
    use EmptyDefaults;
    use EmptyRules;

    #[Rules(['required', 'string', 'min:3', 'max:255'])]
    #[Map(data: 'user_name', transform: 'full_name')]
    public string $name;

    #[Rules(rules: ['required', 'email', 'max:255'], messages: ['email.email' => 'The given email is not a valid email address.'])]
    public string $email;

    #[Rules(['sometimes', 'boolean'])]
    #[DefaultValue(true)]
    public bool $active;
}
