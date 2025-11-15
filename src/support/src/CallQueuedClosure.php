<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Support;

use Closure;
use Hyperf\AsyncQueue\Job;
use Laravel\SerializableClosure\SerializableClosure;
use ReflectionFunction;

use function Hyperf\Support\with;

class CallQueuedClosure extends Job
{
    use Traits\ClosureParameterInjection;

    public string $class = 'Closure';

    public string $method;

    public function __construct(public SerializableClosure $closure)
    {
        $this->method = with(
            new ReflectionFunction($this->closure->getClosure()),
            fn ($reflection) => sprintf(
                '%s:%s-%s',
                str_replace(rtrim(BASE_PATH, '/') . '/', '', $reflection->getFileName()),
                $reflection->getStartLine(),
                $reflection->getEndLine()
            )
        );
    }

    /**
     * @param-closure-this CallQueuedClosure $job
     */
    public static function create(Closure $job): static
    {
        return new static(new SerializableClosure($job));
    }

    public function handle(): mixed
    {
        $parameters = $this->parseClosureParameters($this->closure->getClosure(), []);
        return $this->closure->getClosure()->call($this, ...$parameters);
    }
}
