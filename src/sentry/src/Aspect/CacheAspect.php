<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Aspect;

use Closure;
use FriendsOfHyperf\Sentry\Integration;
use FriendsOfHyperf\Sentry\Switcher;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\Breadcrumb;

use function Hyperf\Support\with;
use function Hyperf\Tappable\tap;

class CacheAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\Cache\Driver\*Driver::fetch',
        'Hyperf\Cache\Driver\*Driver::get',
        'Hyperf\Cache\Driver\*Driver::getMultiple',
        'Hyperf\Cache\Driver\*Driver::set',
        'Hyperf\Cache\Driver\*Driver::setMultiple',
        'Hyperf\Cache\Driver\*Driver::delete',
        'Hyperf\Cache\Driver\*Driver::deleteMultiple',
    ];

    public function __construct(protected Switcher $switcher)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $startTime = microtime(true);

        return tap($proceedingJoinPoint->process(), function ($result) use ($proceedingJoinPoint, $startTime) {
            if (! $this->switcher->isBreadcrumbEnabled('cache')) {
                return;
            }

            $arguments = $proceedingJoinPoint->arguments['keys'];
            $message = match ($proceedingJoinPoint->methodName) {
                'fetch' => (($result[0] ?? false) ? 'Read: ' : 'Missed: ') . ($arguments['key'] ?? ''),
                'get' => (! is_null($result) ? 'Read: ' : 'Missed: ') . ($arguments['key'] ?? ''),
                'getMultiple' => (! empty($result) ? 'Read: ' : 'Missed: ') . implode(', ', (array) ($arguments['keys'] ?? [])),
                'set' => 'Written: ' . ($arguments['key'] ?? ''),
                'setMultiple' => 'Written: ' . implode(', ', array_keys($arguments['keys'] ?? [])),
                'delete' => 'Forgotten: ' . ($arguments['key'] ?? ''),
                'deleteMultiple' => 'Forgotten: ' . implode(', ', (array) ($arguments['keys'] ?? [])),
                default => 'Operation',
            };
            $formattedResult = with(
                match ($proceedingJoinPoint->methodName) {
                    'fetch' => $result[1] ?? '',
                    default => $result,
                },
                fn ($result) => self::displayResult($result)
            );

            Integration::addBreadcrumb(new Breadcrumb(
                Breadcrumb::LEVEL_INFO,
                Breadcrumb::TYPE_DEFAULT,
                'cache',
                $message,
                [
                    'result' => $formattedResult,
                    'arguments' => self::sanitizeArgs($arguments),
                    'timeMs' => round((microtime(true) - $startTime) * 1000, 2),
                ]
            ));
        });
    }

    private static function displayResult(mixed $result): string
    {
        if (is_bool($result)) {
            return $result ? 'true' : 'false';
        }
        if (is_null($result)) {
            return 'null';
        }
        if (is_scalar($result)) {
            return (string) $result;
        }
        return '[' . (is_object($result) ? get_class($result) : gettype($result)) . ']';
    }

    private static function sanitizeArgs(array $args): array
    {
        if (isset($args['callback']) && $args['callback'] instanceof Closure) {
            $args['callback'] = 'Closure';
        }
        if (isset($args['value']) && ! is_scalar($args['value'])) {
            $args['value'] = '[' . (is_object($args['value']) ? get_class($args['value']) : gettype($args['value'])) . ']';
        }
        foreach (['keys', 'values'] as $k) {
            if (isset($args[$k]) && is_array($args[$k])) {
                $max = 20;
                $count = count($args[$k]);
                if ($count > $max) {
                    $args[$k] = array_slice($args[$k], 0, $max, true) + ['...' => sprintf('(+%d more)', $count - $max)];
                }
            } elseif (isset($args[$k]) && is_iterable($args[$k])) {
                // 将可迭代对象折叠为“类型/数量”以避免巨大 payload
                $args[$k] = '[iterable]';
            }
        }
        return $args;
    }
}
