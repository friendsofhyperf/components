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
        if (! $this->telescopeConfig->isEnable('grpc')) {
            return $proceedingJoinPoint->process();
        }
        return match ($proceedingJoinPoint->methodName) {
            'parseMethodParameters' => $this->processParseMethodParameters($proceedingJoinPoint),
            'handleResponse' => $this->processHandleResponse($proceedingJoinPoint),
            default => $proceedingJoinPoint->process(),
        };
    }

    // 处理请求
    protected function processParseMethodParameters(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($result) {
            try {
                /** @var Message $message */
                $message = $result[0];
                $payload = json_decode($message->serializeToJsonString(), true);
            } catch (Throwable $e) {
                return;
            }
            TelescopeContext::setGrpcRequestPayload($payload);
        });
    }

    // 处理响应
    protected function processHandleResponse(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($result) use ($proceedingJoinPoint) {
            /** @var Message|null $message */
            $message = $proceedingJoinPoint->arguments['keys']['message'] ?? null;
            if (is_null($message)) {
                return;
            }
            try {
                $payload = json_decode($message->serializeToJsonString(), true);
            } catch (Throwable $e) {
                return;
            }
            TelescopeContext::setGrpcResponsePayload($payload);
        });
    }
}
