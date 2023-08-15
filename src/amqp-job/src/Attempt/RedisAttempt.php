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
use Hyperf\Redis\Redis;

class RedisAttempt implements Attempt
{
    public function __construct(private Redis $redis, private string $prefix = 'hyperf:amqp-job:attempts:', private int $ttl = 86400)
    {
    }

    public function increment(string $key): int
    {
        $attempts = (int) $this->redis->incr($this->prefix . $key);
        $this->redis->expire($this->prefix . $key, $this->ttl);

        return $attempts;
    }
}
