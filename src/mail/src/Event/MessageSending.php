<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Mail\Event;

use Symfony\Component\Mime\Email;

class MessageSending
{
    public bool $shouldSend = true;

    public function __construct(
        public Email $message,
        public array $data = []
    ) {
    }

    public function setShouldSend(bool $shouldSend): void
    {
        $this->shouldSend = $shouldSend;
    }

    public function shouldSend(): bool
    {
        return $this->shouldSend;
    }
}
