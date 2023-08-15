<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Facade;

use Hyperf\Amqp\Message\ProducerMessageInterface;
use Hyperf\Amqp\Producer as Accessor;

/**
 * @method static bool produce(ProducerMessageInterface $producerMessage, bool $confirm = false, int $timeout = 5)
 */
class AMQP extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Accessor::class;
    }
}
