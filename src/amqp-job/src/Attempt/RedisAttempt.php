<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\AmqpJob\Attempt;

use FriendsOfHyperf\AmqpJob\Contract\Attempt;
use Redis;

class RedisAttempt implements Attempt
{
    private string $prefix = 'hyperf:amqp-job:attempts:';

    public function __construct(private Redis $redis)
    {
    }

    public function incr(string $key): int
    {
        return (int) $this->redis->incr($this->prefix . $key);
    }

    public function clear(string $key): void
    {
        $this->redis->del($this->prefix . $key);
    }
}
