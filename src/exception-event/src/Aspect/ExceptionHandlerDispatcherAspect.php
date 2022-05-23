<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ExceptionEvent\Aspect;

use FriendsOfHyperf\ExceptionEvent\Event\ExceptionDispatched;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * @Aspect
 */
#[Aspect]
class ExceptionHandlerDispatcherAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\\ExceptionHandler\\ExceptionHandlerDispatcher::dispatch',
    ];

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function __construct(ContainerInterface $container)
    {
        $this->eventDispatcher = $container->get(EventDispatcherInterface::class);
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($responseHandled) use ($proceedingJoinPoint) {
            $exception = $proceedingJoinPoint->getArguments()[0][0] ?? null;
            $request = Context::get(ServerRequestInterface::class);
            $response = Context::get(ResponseInterface::class);

            if ($exception && $exception instanceof Throwable) {
                $this->eventDispatcher->dispatch(new ExceptionDispatched($exception, $request, $response));
            }
        });
    }
}
