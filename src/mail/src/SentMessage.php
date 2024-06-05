<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Mail;

use Hyperf\Support\Traits\ForwardsCalls;
use Symfony\Component\Mailer\SentMessage as SymfonySentMessage;

use function Hyperf\Collection\collect;

/**
 * @mixin \Symfony\Component\Mailer\SentMessage
 */
class SentMessage
{
    use ForwardsCalls;

    public function __construct(
        protected SymfonySentMessage $sentMessage
    ) {
    }

    /**
     * Dynamically pass missing methods to the Symfony instance.
     * @param mixed $method
     * @param mixed $parameters
     */
    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->sentMessage, $method, $parameters);
    }

    /**
     * Get the serializable representation of the object.
     */
    public function __serialize(): array
    {
        $hasAttachments = collect($this->sentMessage->getOriginalMessage()->getAttachments())->isNotEmpty(); // @phpstan-ignore-line

        return [
            'hasAttachments' => $hasAttachments,
            'sentMessage' => $hasAttachments ? base64_encode(serialize($this->sentMessage)) : $this->sentMessage,
        ];
    }

    /**
     * Marshal the object from its serialized data.
     */
    public function __unserialize(array $data): void
    {
        $hasAttachments = ($data['hasAttachments'] ?? false) === true;

        $this->sentMessage = $hasAttachments ? unserialize(base64_decode($data['sentMessage'])) : $data['sentMessage'];
    }

    /**
     * Get the underlying Symfony Email instance.
     */
    public function getSymfonySentMessage(): SymfonySentMessage
    {
        return $this->sentMessage;
    }
}
