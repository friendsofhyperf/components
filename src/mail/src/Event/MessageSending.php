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

use FriendsOfHyperf\Mail\Enums\MessageSendingStatus as Status;
use Symfony\Component\Mime\Email;

class MessageSending
{
    public function __construct(
        public Email $message,
        public array $data = [],
        public Status $status = Status::SUCCESS,
    ) {
    }
}
