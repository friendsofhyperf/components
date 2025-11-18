<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\RateLimit;

enum Algorithm: string
{
    case FIXED_WINDOW = 'fixed_window';
    case SLIDING_WINDOW = 'sliding_window';
    case TOKEN_BUCKET = 'token_bucket';
    case LEAKY_BUCKET = 'leaky_bucket';
}
