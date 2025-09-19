<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Mail\Mailable;

class Address
{
    /**
     * @param string $address the recipient's email address
     * @param null|string $name the recipient's name
     */
    public function __construct(
        public string $address,
        public ?string $name = null
    ) {
    }
}
