<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Http\Client\Events;

use FriendsOfHyperf\Http\Client\Request;

class RequestSending
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        /**
         * The request instance.
         */
        public Request $request
    ) {
    }
}
