<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Mail\Factory;

use FriendsOfHyperf\Mail\MailManager;

class MailerFactory
{
    public function __construct(
        protected readonly MailManager $manager
    ) {
    }

    public function __invoke()
    {
        return $this->manager->mailer();
    }
}
