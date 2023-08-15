<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Trigger\Trigger;

use FriendsOfHyperf\Trigger\Contact\TriggerInterface;

abstract class AbstractTrigger implements TriggerInterface
{
    public function onWrite(array $new): void
    {
    }

    public function onUpdate(array $old, array $new): void
    {
    }

    public function onDelete(array $old): void
    {
    }
}
