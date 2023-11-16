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
use Hyperf\Cache\CacheManager;
use Hyperf\Contract\PackerInterface;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

use function Hyperf\Tappable\tap;

/**
 * @property PackerInterface $packer
 */
class CacheAspect extends AbstractAspect
{
    public array $classes = [
        CacheManager::class . '::getDriver',
    ];

    public function __construct(protected SwitchManager $switcherManager)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($driver) {
            if (! $this->switcherManager->isEnable('redis')) {
                return;
            }

            /** @var PackerInterface $packer */
            $packer = (fn () => $this->packer)->call($driver);
            if ($packer instanceof PackerInterface) {
                TelescopeContext::setCachePacker($packer);
            }
        });
    }
}
