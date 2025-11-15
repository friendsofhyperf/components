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

use FriendsOfHyperf\Support\Bus\PendingAmqpProducerMessageDispatch;
use Hyperf\Amqp\Message\ProducerMessageInterface;
use Hyperf\Amqp\Producer;
use Override;

use function FriendsOfHyperf\Support\dispatch;

/**
 * @method static bool produce(ProducerMessageInterface $producerMessage, bool $confirm = false, int $timeout = 5)
 */
class AMQP extends Facade
{
    public function dispatch(ProducerMessageInterface $producerMessage): PendingAmqpProducerMessageDispatch
    {
        return dispatch($producerMessage);
    }

    #[Override]
    protected static function getFacadeAccessor()
    {
        return Producer::class;
    }
}
