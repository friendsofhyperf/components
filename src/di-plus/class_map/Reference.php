<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace Hyperf\Di\Definition;

use Psr\Container\ContainerInterface;

class Reference implements DefinitionInterface, SelfResolvingDefinitionInterface
{
    /**
     * Entry name.
     */
    private string $name = '';

    private bool $needProxy = false;

    /**
     * @param string $targetEntryName name of the target entry
     * @param mixed $sourceEntryName
     */
    public function __construct(private string $targetEntryName, private $sourceEntryName)
    {
    }

    /**
     * Definitions can be cast to string for debugging information.
     */
    public function __toString(): string
    {
        return sprintf('get(%s)', $this->targetEntryName);
    }

    /**
     * Returns the name of the entry in the container.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the name of the entry in the container.
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getTargetEntryName(): string
    {
        return $this->targetEntryName;
    }

    public function getFullTargetEntryName(): string
    {
        return $this->targetEntryName . '@' . $this->sourceEntryName;
    }

    public function resolve(ContainerInterface $container)
    {
        if ($container->has($this->getFullTargetEntryName())) {
            return $container->get($this->getFullTargetEntryName());
        }
        return $container->get($this->getTargetEntryName());
    }

    public function isResolvable(ContainerInterface $container): bool
    {
        return $container->has($this->getFullTargetEntryName()) || $container->has($this->getTargetEntryName());
    }

    /**
     * Determine if the definition need to transfer to a proxy class.
     */
    public function isNeedProxy(): bool
    {
        return $this->needProxy;
    }

    public function setNeedProxy($needProxy): self
    {
        $this->needProxy = $needProxy;
        return $this;
    }
}
