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

use FriendsOfHyperf\ValidatedDTO\Attributes\Rules;
use FriendsOfHyperf\ValidatedDTO\Concerns\EmptyCasts;
use FriendsOfHyperf\ValidatedDTO\Concerns\EmptyDefaults;
use FriendsOfHyperf\ValidatedDTO\Concerns\EmptyRules;
use FriendsOfHyperf\ValidatedDTO\ValidatedDTO;
use Hyperf\Contract\ValidatorInterface;

class NameAfterDTO extends ValidatedDTO
{
    use EmptyCasts;
    use EmptyDefaults;
    use EmptyRules;

    #[Rules(['required', 'string'])]
    public string $first_name;

    #[Rules(['required', 'string'])]
    public string $last_name;

    protected function after(ValidatorInterface $validator): void
    {
        $validator->errors()->add('test', 'After test!');
    }
}
