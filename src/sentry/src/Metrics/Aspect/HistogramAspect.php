<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Metrics\Aspect;

use FriendsOfHyperf\Sentry\Feature;
use FriendsOfHyperf\Sentry\Metrics\Annotation\Histogram;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\Metrics\TraceMetrics;
use Sentry\Unit;

use function Hyperf\Coroutine\defer;
use function Hyperf\Tappable\tap;

class HistogramAspect extends AbstractAspect
{
    public array $classes = [];

    public array $annotations = [
        Histogram::class,
    ];

    public function __construct(protected Feature $feature)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint): mixed
    {
        if (! $this->feature->isMetricsEnabled()) {
            return $proceedingJoinPoint->process();
        }

        $metadata = $proceedingJoinPoint->getAnnotationMetadata();
        $source = $this->fromCamelCase($proceedingJoinPoint->className . '::' . $proceedingJoinPoint->methodName);
        /** @var null|Histogram $annotation */
        $annotation = $metadata->method[Histogram::class] ?? null;
        if ($annotation) {
            $name = $annotation->name ?: $source;
        } else {
            $name = $source;
        }

        $startAt = microtime(true);

        return tap($proceedingJoinPoint->process(), function () use ($name, $proceedingJoinPoint, $startAt) {
            defer(fn () => TraceMetrics::getInstance()->flush());

            TraceMetrics::getInstance()->distribution(
                $name,
                (microtime(true) - $startAt) * 1000,
                [
                    'class' => $proceedingJoinPoint->className,
                    'method' => $proceedingJoinPoint->methodName,
                ],
                Unit::second()
            );
        });
    }

    private function fromCamelCase(string $input): string
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }
}
