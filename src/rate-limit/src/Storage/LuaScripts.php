<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\RateLimit\Storage;

class LuaScripts
{
    /**
     * Fixed window Lua script.
     * Atomically increment counter and set expiration.
     */
    public static function fixedWindow(): string
    {
        return <<<'LUA'
local key = KEYS[1]
local max_attempts = tonumber(ARGV[1])
local decay = tonumber(ARGV[2])
local current_time = tonumber(ARGV[3])

local current = redis.call('get', key)
if current and tonumber(current) >= max_attempts then
    return {0, tonumber(current), redis.call('ttl', key)}
end

local count = redis.call('incr', key)
if count == 1 then
    redis.call('expire', key, decay)
end

return {1, count, redis.call('ttl', key)}
LUA;
    }

    /**
     * Sliding window Lua script.
     * Uses sorted set to track requests with timestamps.
     */
    public static function slidingWindow(): string
    {
        return <<<'LUA'
local key = KEYS[1]
local max_attempts = tonumber(ARGV[1])
local decay = tonumber(ARGV[2])
local current_time = tonumber(ARGV[3])

local window_start = current_time - decay

-- Remove old entries outside the time window
redis.call('zremrangebyscore', key, '-inf', window_start)

-- Count current entries in the window
local current = redis.call('zcard', key)

if current >= max_attempts then
    local oldest = redis.call('zrange', key, 0, 0, 'WITHSCORES')
    local ttl = 0
    if #oldest > 0 then
        ttl = math.ceil(tonumber(oldest[2]) + decay - current_time)
    end
    return {0, current, ttl}
end

-- Add current request
redis.call('zadd', key, current_time, current_time .. ':' .. math.random(1000000, 9999999))
redis.call('expire', key, decay + 1)

return {1, current + 1, decay}
LUA;
    }

    /**
     * Token bucket Lua script.
     * Implements token bucket algorithm.
     */
    public static function tokenBucket(): string
    {
        return <<<'LUA'
local key = KEYS[1]
local capacity = tonumber(ARGV[1])
local refill_rate = tonumber(ARGV[2])
local requested = tonumber(ARGV[3])
local current_time = tonumber(ARGV[4])

local bucket = redis.call('hmget', key, 'tokens', 'last_refill')
local tokens = tonumber(bucket[1])
local last_refill = tonumber(bucket[2])

if tokens == nil then
    tokens = capacity
    last_refill = current_time
end

-- Calculate tokens to add based on time elapsed
local time_elapsed = current_time - last_refill
local tokens_to_add = time_elapsed * refill_rate
tokens = math.min(capacity, tokens + tokens_to_add)

if tokens >= requested then
    tokens = tokens - requested
    redis.call('hmset', key, 'tokens', tokens, 'last_refill', current_time)
    redis.call('expire', key, 3600)
    return {1, math.floor(tokens), 0}
else
    local tokens_needed = requested - tokens
    local wait_time = math.ceil(tokens_needed / refill_rate)
    return {0, math.floor(tokens), wait_time}
end
LUA;
    }

    /**
     * Leaky bucket Lua script.
     * Implements leaky bucket algorithm.
     */
    public static function leakyBucket(): string
    {
        return <<<'LUA'
local key = KEYS[1]
local capacity = tonumber(ARGV[1])
local leak_rate = tonumber(ARGV[2])
local current_time = tonumber(ARGV[3])

local bucket = redis.call('hmget', key, 'water', 'last_leak')
local water = tonumber(bucket[1])
local last_leak = tonumber(bucket[2])

if water == nil then
    water = 0
    last_leak = current_time
end

-- Calculate water leaked based on time elapsed
local time_elapsed = current_time - last_leak
local water_leaked = time_elapsed * leak_rate
water = math.max(0, water - water_leaked)

if water < capacity then
    water = water + 1
    redis.call('hmset', key, 'water', water, 'last_leak', current_time)
    redis.call('expire', key, 3600)
    local remaining = capacity - water
    return {1, math.floor(remaining), 0}
else
    local wait_time = math.ceil((water - capacity + 1) / leak_rate)
    return {0, 0, wait_time}
end
LUA;
    }
}
