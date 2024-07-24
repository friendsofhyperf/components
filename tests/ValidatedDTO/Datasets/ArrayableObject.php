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

use Hyperf\Contract\Arrayable;

class ArrayableObject implements Arrayable
{
    public function key(): string
    {
        return 'arrayable-object-key';
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key(),
        ];
    }
}
