<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Trigger\Contact;

interface TriggerInterface
{
    public function onWrite(array $new): void;

    public function onUpdate(array $old, array $new): void;

    public function onDelete(array $old): void;
}
