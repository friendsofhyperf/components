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

use FriendsOfHyperf\Telescope\IncomingEntry;
use FriendsOfHyperf\Telescope\Telescope;
use FriendsOfHyperf\Telescope\TelescopeConfig;
use FriendsOfHyperf\Telescope\TelescopeContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Crontab\Crontab;
use Hyperf\Crontab\Strategy\Executor;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Psr\Container\ContainerInterface;

use function Hyperf\Tappable\tap;

class ScheduleAspect extends AbstractAspect
{
    public array $classes = [
        Executor::class . '::logResult',
    ];

    public function __construct(
        protected ContainerInterface $container,
        protected ConfigInterface $config,
        protected TelescopeConfig $telescopeConfig,
    ) {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($result) use ($proceedingJoinPoint) {
            if (
                ! $this->telescopeConfig->isEnable('schedule')
            ) {
                return;
            }

            TelescopeContext::getOrSetBatch((string) TelescopeContext::getBatchId());

            /** @var Crontab */
            $crontab = $proceedingJoinPoint->arguments['keys']['crontab'];
            Telescope::recordSchedule(IncomingEntry::make([
                'command' => $crontab->getName(),
                'description' => $crontab->getMemo(),
                'expression' => $crontab->getRule(),
                'timezone' => $crontab->getTimezone(),
                'user' => '-',
                'output' => '',
            ]));
        });
    }
}
