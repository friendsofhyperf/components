<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Support\Traits;

use Closure;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\NormalizerInterface;
use Hyperf\Di\ClosureDefinitionCollectorInterface;
use InvalidArgumentException as GlobalInvalidArgumentException;

trait ClosureParameterInjection
{
    /**
     * @throws GlobalInvalidArgumentException
     */
    final protected function parseClosureParameters(Closure $closure, array $arguments): array
    {
        $container = ApplicationContext::getContainer();

        if (! $container->has(ClosureDefinitionCollectorInterface::class)) {
            return [];
        }

        $definitions = $container->get(ClosureDefinitionCollectorInterface::class)->getParameters($closure);
        $injections = [];

        foreach ($definitions as $pos => $definition) {
            $value = $arguments[$pos] ?? $arguments[$definition->getMeta('name')] ?? null;
            if ($value === null) {
                if ($definition->getMeta('defaultValueAvailable')) {
                    $injections[] = $definition->getMeta('defaultValue');
                } elseif ($container->has($definition->getName())) {
                    $injections[] = $container->get($definition->getName());
                } elseif ($definition->allowsNull()) {
                    $injections[] = null;
                } else {
                    throw new GlobalInvalidArgumentException("Parameter '{$definition->getMeta('name')}' of Closure should not be null");
                }
            } else {
                $injections[] = $container->get(NormalizerInterface::class)->denormalize($value, $definition->getName());
            }
        }

        return $injections;
    }
}
