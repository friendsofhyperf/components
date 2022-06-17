<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ClosureCommand;

use Closure;
use Hyperf\Contract\NormalizerInterface;
use Hyperf\Di\ClosureDefinitionCollectorInterface;
use Hyperf\Di\MethodDefinitionCollectorInterface;
use Psr\Container\ContainerInterface;

class ParameterParser
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * @var ClosureDefinitionCollectorInterface
     */
    private $closureDefinitionCollector;

    /**
     * @var MethodDefinitionCollectorInterface
     */
    private $methodDefinitionCollector;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->normalizer = $this->container->get(NormalizerInterface::class);

        if ($this->container->has(ClosureDefinitionCollectorInterface::class)) {
            $this->closureDefinitionCollector = $this->container->get(ClosureDefinitionCollectorInterface::class);
        }

        if ($this->container->has(MethodDefinitionCollectorInterface::class)) {
            $this->methodDefinitionCollector = $this->container->get(MethodDefinitionCollectorInterface::class);
        }
    }

    /**
     * @throws GlobalInvalidArgumentException
     */
    public function parseClosureParameters(Closure $closure, array $arguments): array
    {
        if (! $this->closureDefinitionCollector) {
            return [];
        }

        $definitions = $this->closureDefinitionCollector->getParameters($closure);

        return $this->getInjections($definitions, 'Closure', $arguments);
    }

    public function parseMethodParameters(string $class, string $method, array $arguments): array
    {
        $definitions = $this->methodDefinitionCollector->getParameters($class, $method);
        return $this->getInjections($definitions, "{$class}::{$method}", $arguments);
    }

    /**
     * @throws GlobalInvalidArgumentException
     */
    private function getInjections(array $definitions, string $callableName, array $arguments): array
    {
        $injections = [];

        foreach ($definitions ?? [] as $pos => $definition) {
            $value = $arguments[$pos] ?? $arguments[$definition->getMeta('name')] ?? null;
            if ($value === null) {
                if ($definition->getMeta('defaultValueAvailable')) {
                    $injections[] = $definition->getMeta('defaultValue');
                } elseif ($this->container->has($definition->getName())) {
                    $injections[] = $this->container->get($definition->getName());
                } elseif ($definition->allowsNull()) {
                    $injections[] = null;
                } else {
                    throw new \InvalidArgumentException("Parameter '{$definition->getMeta('name')}' "
                        . "of {$callableName} should not be null");
                }
            } else {
                $injections[] = $this->normalizer->denormalize($value, $definition->getName());
            }
        }

        return $injections;
    }
}
