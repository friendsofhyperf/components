<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Mail\Transport;

use Hyperf\Collection\Collection;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\RawMessage;

class ArrayTransport implements TransportInterface
{
    /**
     * The collection of Symfony Messages.
     */
    protected Collection $messages;

    /**
     * Create a new array transport instance.
     */
    public function __construct()
    {
        $this->messages = new Collection();
    }

    /**
     * Get the string representation of the transport.
     */
    public function __toString(): string
    {
        return 'array';
    }

    public function send(RawMessage $message, ?Envelope $envelope = null): ?SentMessage
    {
        return $this->messages[] = new SentMessage($message, $envelope ?? Envelope::create($message));
    }

    /**
     * Retrieve the collection of messages.
     */
    public function messages(): Collection
    {
        return $this->messages;
    }

    /**
     * Clear all of the messages from the local collection.
     */
    public function flush(): Collection
    {
        return $this->messages = new Collection();
    }
}
