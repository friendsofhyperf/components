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
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\GrpcClient\GrpcClient;
use Hyperf\GrpcClient\Request;

class GrpcClientAspect extends AbstractAspect
{
    public array $classes = [
        GrpcClient::class . '::send',
    ];

    public function __construct(protected TelescopeConfig $telescopeConfig)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return match ($proceedingJoinPoint->methodName) {
            'send' => $this->processSend($proceedingJoinPoint),
            default => $proceedingJoinPoint->process(),
        };
    }

    private function processSend(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if ($this->telescopeConfig->isEnable('grpc')) {
            $carrier = [];
            $carrier['batch-id'] = TelescopeContext::getBatchId();
            /** @var Request $request */
            $request = $proceedingJoinPoint->arguments['keys']['request'];
            $request->headers = array_merge($request->headers, $carrier);
            $proceedingJoinPoint->arguments['keys']['request'] = $request;
        }

        return $proceedingJoinPoint->process();
    }
}
