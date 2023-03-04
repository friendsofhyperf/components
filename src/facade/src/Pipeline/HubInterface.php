<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Facade\Pipeline;

interface HubInterface
{
    /**
     * Send an object through one of the available pipelines.
     *
     * @param mixed $object
     * @param null|string $pipeline
     * @return mixed
     */
    public function pipe($object, $pipeline = null);
}
