<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope\Aspect;

use FriendsOfHyperf\Telescope\TelescopeConfig;
use FriendsOfHyperf\Telescope\TelescopeContext;
use Google\Protobuf\Internal\Message;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\GrpcServer\CoreMiddleware;
use Throwable;

use function Hyperf\Tappable\tap;

class GrpcCoreMiddlewareAspect extends AbstractAspect
{
    public array $classes = [
        CoreMiddleware::class . '::parseMethodParameters',
        CoreMiddleware::class . '::handleResponse',
    ];

    public function __construct(protected TelescopeConfig $telescopeConfig)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($result) use ($proceedingJoinPoint) {
            if (
                ! $this->telescopeConfig->isEnable('grpc')
                || ! TelescopeContext::getBatchId()
            ) {
                return;
            }

            match ($proceedingJoinPoint->methodName) {
                'parseMethodParameters' => $this->setRequestPayload($result[0] ?? null),
                'handleResponse' => $this->setResponsePayload($proceedingJoinPoint->arguments['keys']['message'] ?? null),
                default => null,
            };
        });
    }

    /**
     * @param null|Message $message
     */
    protected function setRequestPayload($message)
    {
        if (! $message instanceof Message) {
            return;
        }

        try {
            $payload = json_decode($message->serializeToJsonString(), true);
        } catch (Throwable $e) {
            return;
        }

        TelescopeContext::setGrpcRequestPayload($payload);
    }

    /**
     * @param null|Message $message
     */
    protected function setResponsePayload($message)
    {
        if (! $message instanceof Message) {
            return;
        }

        try {
            $payload = json_decode($message->serializeToJsonString(), true);
        } catch (Throwable $e) {
            return;
        }

        TelescopeContext::setGrpcResponsePayload($payload);
    }
}
