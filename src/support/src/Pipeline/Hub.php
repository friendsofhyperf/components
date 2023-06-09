<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Support\Pipeline;

use Closure;
use Hyperf\Pipeline\Pipeline;
use Psr\Container\ContainerInterface;

class Hub implements HubInterface
{
    /**
     * The container implementation.
     */
    protected ?ContainerInterface $container = null;

    /**
     * All of the available pipelines.
     */
    protected array $pipelines = [];

    /**
     * Create a new Hub instance.
     */
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Define the default named pipeline.
     */
    public function defaults(Closure $callback)
    {
        return $this->pipeline('default', $callback);
    }

    /**
     * Define a new named pipeline.
     */
    public function pipeline(string $name, Closure $callback)
    {
        $this->pipelines[$name] = $callback;
    }

    /**
     * Send an object through one of the available pipelines.
     *
     * @param mixed $object
     * @param string|null $pipeline
     * @return mixed
     */
    public function pipe($object, $pipeline = null)
    {
        $pipeline = $pipeline ?: 'default';

        return call_user_func(
            $this->pipelines[$pipeline],
            new Pipeline($this->container),
            $object
        );
    }

    /**
     * Get the container instance used by the hub.
     */
    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }

    /**
     * Set the container instance used by the hub.
     */
    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;

        return $this;
    }
}
