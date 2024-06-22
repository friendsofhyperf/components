<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace PHPSTORM_META;

// Reflect
override(\FriendsOfHyperf\Helpers\app(0), map(['' => '@']));
override(\FriendsOfHyperf\Helpers\di(0), map(['' => '@']));
override(\Hyperf\Context\Context::get(0), map(['' => '@']));
override(\Hyperf\Support\make(0), map(['' => '@']));
override(\Hyperf\Support\optional(0), type(0));
override(\Hyperf\Tappable\tap(0), type(0));
override(\Psr\Container\ContainerInterface::get(0), map([
    '' => '@',
    \Hyperf\Contract\ApplicationInterface::class => \Symfony\Component\Console\Application::class,
]));
override(\FriendsOfHyperf\Tests\Concerns\InteractsWithContainer::mock(0), map(['' => '@']));
override(\FriendsOfHyperf\Tests\Concerns\InteractsWithContainer::swap(0), map(['' => '@']));
override(\FriendsOfHyperf\Tests\Concerns\InteractsWithContainer::instance(0), map(['' => '@']));
override(\FriendsOfHyperf\Tests\Concerns\InteractsWithContainer::partialMock(0), map(['' => '@']));
override(\FriendsOfHyperf\Tests\Concerns\InteractsWithContainer::spy(0), map(['' => '@']));
