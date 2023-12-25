<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace FriendsOfHyperf\Telescope\Aspect;

use FriendsOfHyperf\Telescope\TelescopeConfig;
use FriendsOfHyperf\Telescope\TelescopeContext;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\GrpcServer\CoreMiddleware;
use Throwable;

use function Hyperf\Tappable\tap;

class CoreMiddlewareAspect extends AbstractAspect
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
            if (! $this->telescopeConfig->isEnable('grpc')) {
                return;
            }
            try {
                $req = $result[0];
                $request = $req->serializeToJsonString();
                TelescopeContext::setGrpcRequest($request);
            } catch (Throwable $e) {
                return;
            }
        });
    }

    // 处理响应
    protected function processHandleResponse($proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($result) use ($proceedingJoinPoint) {
            if (! $this->telescopeConfig->isEnable('grpc')) {
                return;
            }
            // 获取参数
            $params = $proceedingJoinPoint->arguments;
            $message = $params['keys']['message'];
            try {
                $response = $message->serializeToJsonString();
                TelescopeContext::setGrpcResponse($response);
            } catch (Throwable $e) {
                return;
            }
        });
    }
}
