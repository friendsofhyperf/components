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

use FriendsOfHyperf\Mail\Contract\Attachable;
use Hyperf\Support\Traits\ForwardsCalls;

/**
 * @mixin Message
 */
class TextMessage
{
    use ForwardsCalls;

    public function __construct(
        protected Message $message
    ) {
    }

    /**
     * Dynamically pass missing methods to the underlying message instance.
     */
    public function __call(string $method, array $parameters): mixed
    {
        $result = $this->forwardCallTo($this->message, $method, $parameters);
        return $result === $this->message ? $this : $result;
    }

    /**
     * Embed a file in the message and get the CID.
     */
    public function embed(string|Attachable|Attachment $file): string
    {
        return '';
    }

    /**
     * Embed in-memory data in the message and get the CID.
     */
    public function embedData(mixed $data, string $name, ?string $contentType = null): string
    {
        return '';
    }
}
