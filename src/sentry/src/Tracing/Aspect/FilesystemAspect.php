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
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Override;
use Sentry\Tracing\SpanContext;

use function Sentry\trace;

class FilesystemAspect extends BaseFilesystemAspect
{
    #[Override]
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->switcher->isTracingEnabled('filesystem')) {
            return $proceedingJoinPoint->process();
        }

        [$op, $description, $data] = $this->getSentryMetadata($proceedingJoinPoint);

        return trace(
            fn () => $proceedingJoinPoint->process(),
            SpanContext::make()
                ->setOp($op)
                ->setData($data)
                ->setOrigin('auto.filesystem')
                ->setDescription($description)
        );
    }
}
