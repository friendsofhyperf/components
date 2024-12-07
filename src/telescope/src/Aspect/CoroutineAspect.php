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

use FriendsOfHyperf\Telescope\TelescopeContext;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Engine\Coroutine as Co;

class CoroutineAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\Coroutine\Coroutine::create',
    ];

    protected array $keys = [
        TelescopeContext::BATCH_ID,
        TelescopeContext::SUB_BATCH_ID,
        \Psr\Http\Message\ServerRequestInterface::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! TelescopeContext::getBatchId()) {
            return $proceedingJoinPoint->process();
        }

        $callable = $proceedingJoinPoint->arguments['keys']['callable'];
        $keys = $this->keys;
        $cid = Co::id();

        $proceedingJoinPoint->arguments['keys']['callable'] = function () use ($callable, $cid, $keys) {
            $from = Co::getContextFor($cid);
            $current = Co::getContextFor();

            foreach ($keys as $key) {
                if (isset($from[$key])) {
                    $current[$key] = $from[$key];
                }
            }

            $callable();
        };

        return $proceedingJoinPoint->process();
    }
}
