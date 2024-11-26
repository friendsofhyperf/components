<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\IpcBroadcaster;

use Closure;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\NormalizerInterface;
use Hyperf\Di\ClosureDefinitionCollectorInterface;
use InvalidArgumentException as GlobalInvalidArgumentException;
use Laravel\SerializableClosure\SerializableClosure;
use Psr\Container\ContainerInterface;

class ClosureIpcMessage extends IpcMessage
{
    protected SerializableClosure $closure;

    public function __construct(Closure $closure)
    {
        $this->closure = new SerializableClosure($closure);
    }

    public function handle(): void
    {
        $parameters = $this->parseClosureParameters($this->closure->getClosure(), []);
        $this->closure->__invoke(...$parameters);
    }

    protected function getContainer(): ContainerInterface
    {
        return ApplicationContext::getContainer();
    }

    protected function getClosureDefinitionCollector(): ClosureDefinitionCollectorInterface
    {
        return $this->getContainer()->get(ClosureDefinitionCollectorInterface::class);
    }

    protected function getNormalizer(): NormalizerInterface
    {
        return $this->getContainer()->get(NormalizerInterface::class);
    }

    /**
     * @throws GlobalInvalidArgumentException
     */
    protected function parseClosureParameters(Closure $closure, array $arguments): array
    {
        if (! $this->getContainer()->has(ClosureDefinitionCollectorInterface::class)) {
            return [];
        }

        $definitions = $this->getClosureDefinitionCollector()->getParameters($closure);

        return $this->getInjections($definitions, 'Closure', $arguments);
    }

    /**
     * @throws GlobalInvalidArgumentException
     */
    protected function getInjections(array $definitions, string $callableName, array $arguments): array
    {
        $injections = [];

        foreach ($definitions as $pos => $definition) {
            $value = $arguments[$pos] ?? $arguments[$definition->getMeta('name')] ?? null;
            if ($value === null) {
                if ($definition->getMeta('defaultValueAvailable')) {
                    $injections[] = $definition->getMeta('defaultValue');
                } elseif ($this->getContainer()->has($definition->getName())) {
                    $injections[] = $this->getContainer()->get($definition->getName());
                } elseif ($definition->allowsNull()) {
                    $injections[] = null;
                } else {
                    throw new GlobalInvalidArgumentException("Parameter '{$definition->getMeta('name')}' "
                        . "of {$callableName} should not be null");
                }
            } else {
                $injections[] = $this->getNormalizer()->denormalize($value, $definition->getName());
            }
        }

        return $injections;
    }
}
