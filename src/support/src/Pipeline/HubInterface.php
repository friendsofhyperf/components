<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Support\Pipeline;

interface HubInterface
{
    /**
     * Send an object through one of the available pipelines.
     *
     * @param mixed $object
     * @param string|null $pipeline
     * @return mixed
     */
    public function pipe($object, $pipeline = null);
}
