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

use FriendsOfHyperf\Oauth2\Server\Enums\OAuth2Grants;
use InvalidArgumentException;
use Stringable;

final class Grant implements Stringable
{
    public function __construct(
        private readonly string $grant
    ) {
        if (! in_array(OAuth2Grants::from($this->grant), OAuth2Grants::cases(), true)) {
            throw new InvalidArgumentException(sprintf('Invalid grant type: %s', $this->grant));
        }
    }

    public function __toString(): string
    {
        return $this->grant;
    }
}
