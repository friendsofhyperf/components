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

use FriendsOfHyperf\Sentry\Tracing\SpanStarter;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\SentrySdk;
use Sentry\Tracing\Span;

use function Hyperf\Tappable\tap;

class CacheAspect extends AbstractAspect
{
    use SpanStarter;

    public array $classes = [
        'Hyperf\Cache\Driver\*Driver::set',
        'Hyperf\Cache\Driver\*Driver::get',
        'Hyperf\Cache\Driver\*Driver::delete',
        'Hyperf\Cache\Driver\*Driver::setMultiple',
        'Hyperf\Cache\Driver\*Driver::getMultiple',
        'Hyperf\Cache\Driver\*Driver::deleteMultiple',
        'Hyperf\Cache\Driver\*Driver::clear',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $parent = SentrySdk::getCurrentHub()->getSpan();

        if (! $parent) {
            return $proceedingJoinPoint->process();
        }

        try {
            $method = $proceedingJoinPoint->methodName;
            $op = match ($method) {
                'set' => 'cache.put',
                'get' => 'cache.get',
                'delete' => 'cache.remove',
                'setMultiple' => 'cache.put',
                'getMultiple' => 'cache.get',
                'deleteMultiple' => 'cache.remove',
                'clear' => 'cache.flush',
                default => 'cache',
            };

            /** @var string|string[] $key */
            $key = match ($method) {
                'set', 'get', 'delete', 'setMultiple', 'getMultiple', 'deleteMultiple' => $proceedingJoinPoint->arguments[0],
                default => '',
            };

            $span = $this->startSpan(op: $op, description: $key);

            return tap($proceedingJoinPoint->process(), function ($value) use ($span, $method, $key) {
                match ($method) {
                    'set', => $this->handleSet($span, $key, $value),
                    'get' => $this->handleGet($span, $key, $value),
                    'delete' => $this->handleDelete($span, $key, $value),
                    'setMultiple' => $this->handleSetMultiple($span, $key, $value),
                    'getMultiple' => $this->handleGetMultiple($span, $key, $value),
                    'deleteMultiple' => $this->handleDeleteMultiple($span, $key, $value),
                    'clear' => $this->handleClear($span),
                    default => null,
                };
            });
        } finally {
            SentrySdk::getCurrentHub()->setSpan($parent);
        }
    }

    private function handleSet(Span $span, string $key, mixed $value)
    {
        $span
            ->setData([
                'cache.key' => $key,
            ])
            ->finish();
    }

    private function handleGet(Span $span, string $key, mixed $value)
    {
        $span
            ->setData([
                'cache.key' => $key,
                'cache.hit' => ! is_null($value),
            ])
            ->finish();
    }

    private function handleDelete(Span $span, string $key, mixed $value)
    {
        $span
            ->setData([
                'cache.key' => $key,
            ])
            ->finish();
    }

    private function handleSetMultiple(Span $span, array $keys, array $values)
    {
        $span
            ->setData([
                'cache.key' => $keys,
            ])
            ->finish();
    }

    private function handleGetMultiple(Span $span, array $keys, array $values)
    {
        $span
            ->setData([
                'cache.key' => $keys,
                'cache.hit' => ! empty($values),
            ])
            ->finish();
    }

    private function handleDeleteMultiple(Span $span, array $keys, array $values)
    {
        $span
            ->setData([
                'cache.key' => $keys,
            ])
            ->finish();
    }

    private function handleClear(Span $span)
    {
        $span
            ->setData([
                // 'network.peer.address' => '127.0.0.1',
                // 'network.peer.port' => 9000,
            ])
            ->finish();
    }
}
