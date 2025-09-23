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

use FriendsOfHyperf\Sentry\Aspect\FilesystemAspect as BaseFilesystemAspect;
use FriendsOfHyperf\Sentry\Tracing\SpanStarter;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Override;
use Sentry\State\Scope;
use Sentry\Tracing\SpanContext;
use Sentry\Tracing\SpanStatus;
use Throwable;

class FilesystemAspect extends BaseFilesystemAspect
{
    use SpanStarter;

    #[Override]
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->switcher->isTracingEnabled('filesystem')) {
            return $proceedingJoinPoint->process();
        }

        [$op, $description, $data] = $this->getSentryMetadata($proceedingJoinPoint);

        return $this->trace(
            function (Scope $scope) use ($proceedingJoinPoint) {
                $span = $scope->getSpan();
                try {
                    return $proceedingJoinPoint->process();
                } catch (Throwable $exception) {
                    $span->setStatus(SpanStatus::internalError())
                        ->setTags([
                            'error' => 'true',
                            'exception.class' => $exception::class,
                            'exception.message' => $exception->getMessage(),
                            'exception.code' => (string) $exception->getCode(),
                        ]);
                    if ($this->switcher->isTracingExtraTagEnabled('exception.stack_trace')) {
                        $span->setData([
                            'exception.stack_trace' => (string) $exception,
                        ]);
                    }
                    throw $exception;
                }
            },
            SpanContext::make()
                ->setOp($op)
                ->setDescription($description)
                ->setOrigin('auto.filesystem')
                ->setData($data)
        );
    }
}
