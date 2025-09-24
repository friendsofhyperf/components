<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Tracing\Aspect;

use FriendsOfHyperf\Sentry\Switcher;
use FriendsOfHyperf\Sentry\Tracing\SpanStarter;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\State\Scope;
use Sentry\Tracing\SpanContext;

use function Hyperf\Tappable\tap;

/**
 * @see https://develop.sentry.dev/sdk/telemetry/traces/modules/caches/
 */
class CacheAspect extends AbstractAspect
{
    use SpanStarter;

    public array $classes = [
        'Hyperf\Cache\Driver\*Driver::set',
        'Hyperf\Cache\Driver\*Driver::setMultiple',
        'Hyperf\Cache\Driver\*Driver::fetch',
        'Hyperf\Cache\Driver\*Driver::get',
        'Hyperf\Cache\Driver\*Driver::getMultiple',
        'Hyperf\Cache\Driver\*Driver::delete',
        'Hyperf\Cache\Driver\*Driver::deleteMultiple',
        'Hyperf\Cache\Driver\*Driver::clear',
    ];

    public function __construct(protected Switcher $switcher)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->switcher->isTracingSpanEnabled('cache')) {
            return $proceedingJoinPoint->process();
        }

        $method = $proceedingJoinPoint->methodName;
        $op = match ($method) {
            'set', 'setMultiple' => 'cache.put',
            'get', 'fetch', 'getMultiple' => 'cache.get',
            'delete', 'deleteMultiple' => 'cache.remove',
            'clear' => 'cache.flush',
            default => 'cache',
        };

        $arguments = $proceedingJoinPoint->arguments['keys'] ?? [];

        /** @var string[] $keys */
        $keys = match ($method) {
            'set', 'get', 'delete' => [$arguments['key'] ?? 'unknown'],
            'setMultiple' => array_keys($arguments['values'] ?? []),
            'getMultiple', 'deleteMultiple' => $arguments['keys'] ?? [],
            default => [],
        };

        return $this->trace(
            function (Scope $scope) use ($proceedingJoinPoint, $method) {
                return tap($proceedingJoinPoint->process(), function ($result) use ($method, $scope) {
                    $data = match ($method) {
                        'get' => [
                            'cache.hit' => ! is_null($result),
                            'cache.item_size' => strlen((string) json_encode($result)),
                        ],
                        'fetch' => [
                            'cache.hit' => ($result[0] ?? false) !== false,
                            'cache.item_size' => strlen((string) json_encode($result[1] ?? '')),
                        ],
                        'getMultiple' => [
                            'cache.hit' => ! empty($result),
                            'cache.item_size' => strlen((string) json_encode(array_values((array) $result))),
                        ],
                        default => [],
                    };
                    $scope->getSpan()?->setData($data);
                });
            },
            SpanContext::make()
                ->setOp($op)
                ->setDescription(implode(', ', $keys))
                ->setOrigin('auto.cache')
                ->setData([
                    'coroutine.id' => Coroutine::id(),
                    'cache.key' => $keys,
                    'cache.ttl' => $arguments['ttl'] ?? null,
                    'item_size' => match (true) {
                        isset($arguments['value']) => strlen(json_encode($arguments['value'])),
                        isset($arguments['values']) && is_array($arguments['values']) => strlen(json_encode(array_values($arguments['values']))),
                        default => 0,
                    },
                ])
        );
    }
}
