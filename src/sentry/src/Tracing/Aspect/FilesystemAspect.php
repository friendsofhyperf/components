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
            fn (Scope $scope) => $proceedingJoinPoint->process(),
            SpanContext::make()
                ->setOp($op)
                ->setDescription($description)
                ->setOrigin('auto.filesystem')
                ->setData($data)
        );
    }
}
