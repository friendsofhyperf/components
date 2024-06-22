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
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Event\EventDispatcher;
use Hyperf\Stringable\Str;
use Psr\EventDispatcher\ListenerProviderInterface;
use ReflectionClass;

use function Hyperf\Collection\collect;
use function Hyperf\Tappable\tap;

class EventAspect extends AbstractAspect
{
    public array $classes = [
        EventDispatcher::class . '::dispatch',
    ];

    public function __construct(protected TelescopeConfig $telescopeConfig, protected ListenerProviderInterface $listeners)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($result) use ($proceedingJoinPoint) {
            if (! $this->telescopeConfig->isEnable('event')) {
                return;
            }

            $event = $proceedingJoinPoint->arguments['keys']['event'];
            $eventName = get_class($event);
            $listenerNames = [];
            foreach ($this->listeners->getListenersForEvent($event) as $listener) {
                $listenerNames[] = $this->getListenerName($listener);
            }
            if (Str::startsWith($eventName, 'Hyperf\\')) {
                return;
            }
            $ref = new ReflectionClass($event);

            $constructor = $ref->getConstructor();
            $payload = $constructor->getParameters();
            $payload = $this->extractPayload($eventName, $payload);

            Telescope::recordEvent(IncomingEntry::make([
                'name' => $eventName,
                'listeners' => $listenerNames,
                'payload' => $payload,
                'hash' => md5($eventName),
            ]));
        });
    }

    protected function extractPayload($eventName, $payload): array
    {
        return collect($payload)->map(function ($value) {
            return is_object($value) ? [
                'class' => get_class($value),
                'properties' => json_decode(json_encode($value), true),
            ] : $value;
        })->toArray();
    }

    protected function getListenerName($listener): array
    {
        $listenerName = '[ERROR TYPE]';
        if (is_array($listener)) {
            $listenerName = is_string($listener[0]) ? $listener[0] : get_class($listener[0]);
        } elseif (is_string($listener)) {
            $listenerName = $listener;
        } elseif (is_object($listener)) {
            $listenerName = get_class($listener);
        }
        return ['name' => $listenerName];
    }
}
