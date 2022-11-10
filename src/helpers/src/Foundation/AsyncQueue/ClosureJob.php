<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Helpers\Foundation\AsyncQueue;

use Closure;
use Hyperf\AsyncQueue\Job;
use Hyperf\Contract\NormalizerInterface;
use Hyperf\Di\ClosureDefinitionCollectorInterface;
use Hyperf\Utils\ApplicationContext;
use InvalidArgumentException as GlobalInvalidArgumentException;
use Opis\Closure\SerializableClosure;
use Psr\Container\ContainerInterface;
use ReflectionFunction;

class ClosureJob extends Job
{
    /**
     * @var string
     */
    public $class;

    /**
     * @var string
     */
    public $method;

    /**
     * @var SerializableClosure
     */
    protected $closure;

    public function __construct(Closure $closure, int $maxAttempts = 0)
    {
        $this->closure = new SerializableClosure($closure);
        $this->maxAttempts = $maxAttempts;
        $this->class = 'Closure';
        $this->method = with(new ReflectionFunction($this->closure->getClosure()), function ($reflection) {
            return sprintf('%s:%s', str_replace(rtrim(BASE_PATH, '/') . '/', '', $reflection->getFileName()), $reflection->getStartLine());
        });
    }

    public function handle()
    {
        $closure = $this->closure->getClosure();
        $parameters = $this->parseClosureParameters($closure, []);

        call($closure, $parameters);
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

        foreach ($definitions ?? [] as $pos => $definition) {
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
