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
                'set' => 'cache.put',
                'get', 'fetch' => 'cache.get',
                'delete' => 'cache.remove',
                'setMultiple' => 'cache.put',
                'getMultiple' => 'cache.get',
                'deleteMultiple' => 'cache.remove',
                'clear' => 'cache.flush',
                default => 'cache',
            };

            /** @var string|string[] $key */
            [$key, $ttl] = match ($method) {
                'set', 'get', 'delete' => [
                    $proceedingJoinPoint->arguments['keys']['key'] ?? 'unknown',
                    $proceedingJoinPoint->arguments['keys']['ttl'] ?? null,
                ],
                'setMultiple', 'getMultiple', 'deleteMultiple' => [
                    $proceedingJoinPoint->arguments['keys']['keys'] ?? [],
                    $proceedingJoinPoint->arguments['keys']['ttl'] ?? null,
                ],
                default => ['', null],
            };

            $span = $this->startSpan(
                op: $op,
                description: implode(', ', (array) $key),
                asParent: true
            );

            return tap($proceedingJoinPoint->process(), function ($result) use ($span, $method, $key, $ttl) {
                $data = match ($method) {
                    'set', => ['cache.key' => $key, 'cache.ttl' => $ttl],
                    'get', 'fetch' => ['cache.key' => $key, 'cache.hit' => ! is_null($result)],
                    'delete' => ['cache.key' => $key],
                    'setMultiple' => ['cache.key' => $key, 'cache.ttl' => $ttl],
                    'getMultiple' => ['cache.key' => $key, 'cache.hit' => ! empty($result)],
                    'deleteMultiple' => ['cache.key' => $key],
                    'clear' => [],
                    default => [],
                };
                $span->setOrigin('auto.cache')->setData($data)->finish();
            });
        } finally {
            SentrySdk::getCurrentHub()->setSpan($parent);
        }
    }
}
