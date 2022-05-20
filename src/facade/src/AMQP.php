<?php

declare(strict_types=1);
/**
 * This file is part of hyperf/facade.
 *
 * @link     https://github.com/friendsofhyperf/facade
 * @document https://github.com/friendsofhyperf/facade/blob/1.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Facade;

use Hyperf\Amqp\Message\ProducerMessageInterface;
use Hyperf\Amqp\Producer as Accessor;

/**
 * @method static produce(ProducerMessageInterface $producerMessage, bool $confirm = false, int $timeout = 5)
 */
class AMQP extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Accessor::class;
    }
}
