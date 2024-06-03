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

use Closure;
use FriendsOfHyperf\Mailer\Mailable;
use FriendsOfHyperf\Mailer\PendingMail;
use FriendsOfHyperf\Mailer\SentMessage;

interface Mailer
{
    /**
     * Begin the process of mailing a mailable class instance.
     */
    public function to(mixed $users): PendingMail;

    /**
     * Begin the process of mailing a mailable class instance.
     */
    public function bcc(mixed $users): ?PendingMail;

    /**
     * Send a new message with only a raw text part.
     */
    public function raw(string $text, mixed $callback): ?SentMessage;

    /**
     * Send a new message using a view.
     */
    public function send(Mailable|string|array $view, array $data = [], Closure|string|null $callback = null): ?SentMessage;
}
