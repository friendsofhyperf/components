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

use FriendsOfHyperf\Sentry\Annotation\Graceful;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\SentrySdk;
use Throwable;

use function Hyperf\Support\value;

class GracefulAspect extends AbstractAspect
{
    public array $annotations = [
        Graceful::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint): mixed
    {
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();

        /** @var null|Graceful $annotation */
        $annotation = $metadata->method[Graceful::class] ?? null;

        if ($annotation === null) {
            return $proceedingJoinPoint->process();
        }

        try {
            return $proceedingJoinPoint->process();
        } catch (Throwable $e) {
            return match ($annotation->strategy) {
                Graceful::STRATEGY_FALLBACK => value($annotation->fallback, $proceedingJoinPoint, $e),
                Graceful::STRATEGY_SWALLOW => null,
                Graceful::STRATEGY_RETHROW => throw $e,
                Graceful::STRATEGY_TRANSLATE => throw new ($annotation->mapTo ?? Throwable::class)('Translated Exception', 0, $e),
                default => null,
            };
        } finally {
            if (isset($e) && $annotation->report) {
                SentrySdk::getCurrentHub()->captureException($e);
            }
        }
    }
}
