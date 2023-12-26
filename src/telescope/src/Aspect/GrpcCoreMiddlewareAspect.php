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
    protected function processParseMethodParameters($proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($result) {
            try {
                $req = $result[0];
                $requestPayload = $req->serializeToJsonString();
            } catch (Throwable $e) {
                return;
            }
            TelescopeContext::setGrpcRequest($requestPayload);
        });
    }

    // 处理响应
    protected function processHandleResponse($proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function () use ($proceedingJoinPoint) {
            // 获取参数
            /** @var ?Message $message */
            $message = $proceedingJoinPoint->arguments['keys']['message'];
            if (is_null($message)) {
                return;
            }
            try {
                $responsePayload = $message->serializeToJsonString();
            } catch (Throwable $e) {
                return;
            }
            TelescopeContext::setGrpcResponse($responsePayload);
        });
    }
}
