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
use FriendsOfHyperf\Sentry\Metrics\Annotation\Counter;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\Metrics\TraceMetrics;

use function Hyperf\Coroutine\defer;

class CounterAspect extends AbstractAspect
{
    public array $classes = [];

    public array $annotations = [
        Counter::class,
    ];

    public function __construct(protected Feature $feature)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint): mixed
    {
        if ($this->feature->isMetricsEnabled()) {
            $metadata = $proceedingJoinPoint->getAnnotationMetadata();
            $source = $this->fromCamelCase($proceedingJoinPoint->className . '::' . $proceedingJoinPoint->methodName);

            /** @var null|Counter $annotation */
            $annotation = $metadata->method[Counter::class] ?? null;

            if ($annotation) {
                $name = $annotation->name ?: $source;
            } else {
                $name = $source;
            }

            defer(fn () => TraceMetrics::getInstance()->flush());

            TraceMetrics::getInstance()
                ->count($name, 1, [
                    'class' => $proceedingJoinPoint->className,
                    'method' => $proceedingJoinPoint->methodName,
                ]);
        }

        return $proceedingJoinPoint->process();
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
