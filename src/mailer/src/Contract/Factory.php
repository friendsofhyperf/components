<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Mailer\Contract;

interface Factory
{
    /**
     * Get a mailer instance by name.
     *
     * @return Mailer
     */
    public function mailer(?string $name = null): \FriendsOfHyperf\Mailer\Mailer;
}
