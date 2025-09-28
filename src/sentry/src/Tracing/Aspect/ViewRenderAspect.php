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

use FriendsOfHyperf\Sentry\Feature;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\State\Scope;
use Sentry\Tracing\SpanContext;

use function FriendsOfHyperf\Sentry\trace;

/**
 * @property string $mode
 * @property string $engine
 */
class ViewRenderAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\View\Render::render',
    ];

    public function __construct(protected Feature $feature)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->feature->isTracingSpanEnabled('view')) {
            return $proceedingJoinPoint->process();
        }

        /** @var \Hyperf\View\Render $instance */
        $instance = $proceedingJoinPoint->getInstance();
        [$mode, $engine] = (fn () => [$this->mode ?? '<missing_mode>', $this->engine ?? '<missing_engine>'])->call($instance);
        $arguments = $proceedingJoinPoint->arguments['keys'] ?? [];
        /** @var string $template */
        $template = $arguments['template'] ?? 'unknown';
        $data = [
            'view.mode' => $mode,
            'view.engine' => $engine,
            'view.template' => $template,
            'view.data' => $arguments['data'] ?? [],
        ];

        return trace(
            fn (Scope $scope) => $proceedingJoinPoint->process(),
            SpanContext::make()
                ->setOp('view.render')
                ->setDescription($template)
                ->setOrigin('auto.view')
                ->setData($data)
        );
    }
}
