<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Listener;

use FriendsOfHyperf\Sentry\Integration;
use FriendsOfHyperf\Sentry\Switcher;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Redis\Event\CommandExecuted;
use Sentry\Breadcrumb;

class RedisCommandExecutedListener implements ListenerInterface
{
    public function __construct(
        protected ConfigInterface $config,
        protected Switcher $switcher
    ) {
        $this->setRedisEventEnable();
    }

    public function listen(): array
    {
        return [
            CommandExecuted::class,
        ];
    }

    /**
     * @param object|CommandExecuted $event
     */
    public function process(object $event): void
    {
        if (
            ! $this->switcher->isBreadcrumbEnable('redis')
            || ! $event instanceof CommandExecuted
        ) {
            return;
        }

        Integration::addBreadcrumb(new Breadcrumb(
            Breadcrumb::LEVEL_INFO,
            Breadcrumb::TYPE_DEFAULT,
            'redis',
            $event->command,
            [
                'result' => $event->result,
                'arguments' => $event->parameters,
                'timeMs' => $event->time * 1000,
            ]
        ));
    }

    private function setRedisEventEnable()
    {
        foreach ((array) $this->config->get('redis', []) as $connection => $_) {
            $this->config->set('redis.' . $connection . '.event.enable', true);
        }
    }
}
