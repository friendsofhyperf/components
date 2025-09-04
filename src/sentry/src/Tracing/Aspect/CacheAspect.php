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
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\SentrySdk;

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
        if (! $this->switcher->isTracingSpanEnable('cache') || Switcher::isDisableCoroutineTracing()) {
            return $proceedingJoinPoint->process();
        }

        $parent = SentrySdk::getCurrentHub()->getSpan();

        try {
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

            $span = $this->startSpan(
                op: $op,
                description: implode(', ', $keys),
                origin: 'auto.cache',
                asParent: true
            )?->setData([
                'cache.key' => $keys,
                'cache.ttl' => $arguments['ttl'] ?? null,
                'item_size' => match (true) {
                    isset($arguments['value']) => strlen(json_encode($arguments['value'])),
                    isset($arguments['values']) && is_array($arguments['values']) => strlen(json_encode(array_values($arguments['values']))),
                    default => 0,
                },
            ]);

            return tap($proceedingJoinPoint->process(), function ($result) use ($span, $method) {
                $data = match ($method) {
                    'get', 'fetch' => [
                        'cache.hit' => ! is_null($result),
                        'cache.item_size' => strlen((string) json_encode($result)),
                    ],
                    'getMultiple' => [
                        'cache.hit' => ! empty($result),
                        'cache.item_size' => strlen((string) json_encode(array_values((array) $result))),
                    ],
                    default => [],
                };
                $span?->setData($data)->finish();
            });
        } finally {
            SentrySdk::getCurrentHub()->setSpan($parent);
        }
    }
}
