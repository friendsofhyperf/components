<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Oauth2\Server\ValueObject;

use RuntimeException;

class RedirectUri
{
    public function __construct(
        private string $redirectUri
    ) {
        if (! filter_var($this->redirectUri, FILTER_VALIDATE_URL)) {
            throw new RuntimeException(\sprintf('The \'%s\' string is not a valid URI.', $redirectUri));
        }
    }

    public function __toString(): string
    {
        return $this->redirectUri;
    }
}
