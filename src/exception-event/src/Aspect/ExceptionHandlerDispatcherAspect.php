<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ExceptionEvent\Aspect;

use FriendsOfHyperf\ExceptionEvent\Event\ExceptionDispatched;
use Hyperf\Context\Context;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\ExceptionHandler\ExceptionHandlerDispatcher;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

use function Hyperf\Tappable\tap;

class ExceptionHandlerDispatcherAspect extends AbstractAspect
{
    public array $classes = [
        ExceptionHandlerDispatcher::class . '::dispatch',
    ];

    public function __construct(protected EventDispatcherInterface $eventDispatcher)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($responseHandled) use ($proceedingJoinPoint) {
            /** @var Throwable|null $exception */
            $exception = $proceedingJoinPoint->getArguments()[0][0] ?? null;
            $request = Context::get(ServerRequestInterface::class);
            $response = Context::get(ResponseInterface::class);

            if ($exception && $exception instanceof Throwable) {
                $this->eventDispatcher->dispatch(new ExceptionDispatched($exception, $request, $response));
            }
        });
    }
}
