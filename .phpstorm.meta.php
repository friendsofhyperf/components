<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace PHPSTORM_META;

    // Reflect
    override(\Psr\Container\ContainerInterface::get(0), map(['' => '@']));
    override(\Hyperf\Context\Context::get(0), map(['' => '@']));
    override(\app(0), map(['' => '@']));
    override(\di(0), map(['' => '@']));
    override(\resolve(0), map(['' => '@']));
    override(\make(0), map(['' => '@']));
    override(\optional(0), type(0));
    override(\tap(0), type(0));
