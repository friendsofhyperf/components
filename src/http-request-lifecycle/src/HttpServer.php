<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Http\RequestLifeCycle;

use FriendsOfHyperf\Http\RequestLifeCycle\Events\RequestHandled;
use FriendsOfHyperf\Http\RequestLifeCycle\Events\RequestReceived;
use FriendsOfHyperf\Http\RequestLifeCycle\Events\RequestTerminated;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\HttpMessage\Server\Response as Psr7Response;
use Hyperf\HttpServer\MiddlewareManager;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Support\SafeCaller;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

use function Hyperf\Coroutine\defer;

class HttpServer extends \Hyperf\HttpServer\Server
{
    public function onRequest($request, $response): void
    {
        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $this->container->get(EventDispatcherInterface::class);

        try {
            CoordinatorManager::until(Constants::WORKER_START)->yield();

            [$psr7Request, $psr7Response] = $this->initRequestAndResponse($request, $response);

            $eventDispatcher->dispatch(new RequestReceived($psr7Request, $psr7Response));

            $psr7Request = $this->coreMiddleware->dispatch($psr7Request);
            /** @var Dispatched $dispatched */
            $dispatched = $psr7Request->getAttribute(Dispatched::class);
            $middlewares = $this->middlewares;

            if ($dispatched->isFound()) {
                $registeredMiddlewares = MiddlewareManager::get($this->serverName, $dispatched->handler->route, $psr7Request->getMethod());
                $middlewares = array_merge($middlewares, $registeredMiddlewares);
            }

            $psr7Response = $this->dispatcher->dispatch($psr7Request, $middlewares, $this->coreMiddleware);
        } catch (Throwable $throwable) {
            // Delegate the exception to exception handler.
            $psr7Response = $this->container->get(SafeCaller::class)->call(function () use ($throwable) {
                return $this->exceptionHandlerDispatcher->dispatch($throwable, $this->exceptionHandlers);
            }, static function () {
                return (new Psr7Response())->withStatus(400);
            });
        } finally {
            if (isset($psr7Request)) {
                defer(fn () => $eventDispatcher->dispatch(new RequestTerminated($psr7Request, $psr7Response)));

                $eventDispatcher->dispatch(new RequestHandled($psr7Request, $psr7Response));
            }

            // Send the Response to client.
            if (! isset($psr7Response) || ! $psr7Response instanceof ResponseInterface) {
                return;
            }

            if (isset($psr7Request) && $psr7Request->getMethod() === 'HEAD') {
                $this->responseEmitter->emit($psr7Response, $response, false);
            } else {
                $this->responseEmitter->emit($psr7Response, $response, true);
            }
        }
    }
}
