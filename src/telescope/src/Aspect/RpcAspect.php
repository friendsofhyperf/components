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

use FriendsOfHyperf\Telescope\SwitchManager;
use FriendsOfHyperf\Telescope\TelescopeContext;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Rpc\Context;
use Hyperf\RpcClient\AbstractServiceClient;

use function Hyperf\Tappable\tap;

class RpcAspect extends AbstractAspect
{
    public array $classes = [
        AbstractServiceClient::class . '::__generateRpcPath',
    ];

    public function __construct(protected SwitchManager $switcherManager, protected Context $context)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function () {
            if (static::class == self::class && $this->switcherManager->isEnable('rpc') === false) {
                return;
            }

            $carrier = [];
            $carrier['batch-id'] = TelescopeContext::getBatchId();
            $this->context->set('telescope.carrier', $carrier);
        });
    }
}
