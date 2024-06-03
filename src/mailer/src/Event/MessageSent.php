<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Mailer\Event;

use Exception;
use FriendsOfHyperf\Mailer\SentMessage;

use function Hyperf\Collection\collect;

class MessageSent
{
    public function __construct(
        public SentMessage $sent,
        public array $data = []
    ) {
    }

    /**
     * Get the serializable representation of the object.
     */
    public function __serialize(): array
    {
        $hasAttachments = collect($this->message->getAttachments())->isNotEmpty();

        return [
            'sent' => $this->sent,
            'data' => $hasAttachments ? base64_encode(serialize($this->data)) : $this->data,
            'hasAttachments' => $hasAttachments,
        ];
    }

    /**
     * Marshal the object from its serialized data.
     */
    public function __unserialize(array $data): void
    {
        $this->sent = $data['sent'];

        $this->data = (($data['hasAttachments'] ?? false) === true)
            ? unserialize(base64_decode($data['data']))
            : $data['data'];
    }

    /**
     * Dynamically get the original message.
     * @param mixed $key
     */
    public function __get($key)
    {
        if ($key === 'message') {
            return $this->sent->getOriginalMessage();
        }

        throw new Exception('Unable to access undefined property on ' . __CLASS__ . ': ' . $key);
    }
}
