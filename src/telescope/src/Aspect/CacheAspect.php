<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope\Aspect;

use FriendsOfHyperf\Telescope\IncomingEntry;
use FriendsOfHyperf\Telescope\Telescope;
use FriendsOfHyperf\Telescope\TelescopeConfig;
use FriendsOfHyperf\Telescope\TelescopeContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\PackerInterface;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Stringable\Str;

use function Hyperf\Tappable\tap;

/**
 * @property PackerInterface $packer
 */
class CacheAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\Cache\CacheManager::getDriver',
        'Hyperf\Cache\Driver\*Driver::fetch',
        'Hyperf\Cache\Driver\*Driver::get',
        'Hyperf\Cache\Driver\*Driver::set',
    ];

    public function __construct(
        protected ConfigInterface $config,
        protected TelescopeConfig $telescopeConfig
    ) {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (Str::startsWith($proceedingJoinPoint->className, 'Hyperf\Cache\Driver\\')) {
            return match ($proceedingJoinPoint->methodName) {
                'fetch' => $this->processDriverFetch($proceedingJoinPoint),
                'get' => $this->processDriverGet($proceedingJoinPoint),
                'set' => $this->processDriverSet($proceedingJoinPoint),
                default => $proceedingJoinPoint->process(),
            };
        }

        return $this->processGetDriver($proceedingJoinPoint);
    }

    protected function processGetDriver($proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($driver) use ($proceedingJoinPoint) {
            if (
                ! $this->telescopeConfig->isEnable('redis')
                || ! TelescopeContext::getBatchId()
            ) {
                return;
            }

            $name = $proceedingJoinPoint->arguments['keys']['name'];
            TelescopeContext::setCacheDriver($name);
        });
    }

    protected function processDriverFetch($proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($result) use ($proceedingJoinPoint) {
            if (! $this->telescopeConfig->isEnable('cache')) {
                return;
            }

            $arguments = $proceedingJoinPoint->arguments['keys'];
            [$has, $data] = $result;
            Telescope::recordCache(IncomingEntry::make([
                'type' => $has ? 'hit' : 'missed',
                'key' => $this->getCacheKey($arguments['key']),
                'value' => $data,
            ]));
        });
    }

    protected function processDriverGet($proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($result) use ($proceedingJoinPoint) {
            if (! $this->telescopeConfig->isEnable('cache')) {
                return;
            }

            $arguments = $proceedingJoinPoint->arguments['keys'];
            Telescope::recordCache(IncomingEntry::make([
                'type' => is_null($result) ? 'missed' : 'hit',
                'key' => $this->getCacheKey($arguments['key']),
                'value' => $result,
            ]));
        });
    }

    protected function processDriverSet($proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function () use ($proceedingJoinPoint) {
            if (! $this->telescopeConfig->isEnable('cache')) {
                return;
            }

            $arguments = $proceedingJoinPoint->arguments['keys'];
            Telescope::recordCache(IncomingEntry::make([
                'type' => 'set',
                'key' => $this->getCacheKey($arguments['key']),
                'value' => $arguments['value'],
            ]));
        });
    }

    protected function getCacheKey(string $key): string
    {
        $driver = TelescopeContext::getCacheDriver();
        return $this->config->get('cache.' . $driver . '.prefix', '') . $key;
    }
}
