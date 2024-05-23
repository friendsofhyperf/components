<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Notification\Stubs;

use FriendsOfHyperf\Notification\Traits\Notifiable;
use Hyperf\Database\Model\Model;

class NotifiableUser extends Model
{
    use Notifiable;
}
