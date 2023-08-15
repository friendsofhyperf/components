<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\GatewayWorker;

use GatewayClient\Gateway;
use Hyperf\Macroable\Macroable;

class Client extends Gateway
{
    use Macroable;
}
