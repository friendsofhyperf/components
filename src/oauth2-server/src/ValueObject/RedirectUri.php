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
use Stringable;

final class RedirectUri implements Stringable
{
    public function __construct(
        private readonly string $redirectUri
    ) {
        if (! $this->valid($redirectUri)) {
            throw new RuntimeException(\sprintf('The \'%s\' string is not a valid OAuth2 redirect URI.', $redirectUri));
        }
    }

    public function __toString(): string
    {
        return $this->redirectUri;
    }

    private function valid(string $uri): bool
    {
        // Basic URL validation
        if (! filter_var($uri, FILTER_VALIDATE_URL)) {
            return false;
        }

        $parsed = parse_url($uri);

        // OAuth2 RFC 6749 requirements
        // 1. Must not contain fragments
        if (isset($parsed['fragment'])) {
            return false;
        }

        // 2. For security, consider restricting schemes
        $allowedSchemes = ['https', 'http']; // Add custom schemes as needed
        if (! in_array($parsed['scheme'] ?? '', $allowedSchemes, true)) {
            return false;
        }

        return true;
    }
}
