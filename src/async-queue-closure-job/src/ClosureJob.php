<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\AsyncQueueClosureJob;

use Closure;
use Hyperf\AsyncQueue\Job;
use Laravel\SerializableClosure\SerializableClosure;
use ReflectionFunction;

use function Hyperf\Support\with;

class ClosureJob extends Job
{
    use ClosureParameterInjection;

    public string $class = 'Closure';

    public string $method;

    protected SerializableClosure $closure;

    public function __construct(Closure $closure, int $maxAttempts = 0)
    {
        $this->closure = new SerializableClosure($closure);
        $this->maxAttempts = $maxAttempts;
        $this->method = with(new ReflectionFunction($this->closure->getClosure()), fn ($reflection) => sprintf('%s:%s', str_replace(rtrim(BASE_PATH, '/') . '/', '', $reflection->getFileName()), $reflection->getStartLine()));
    }

    public function handle(): mixed
    {
        $parameters = $this->parseClosureParameters($this->closure->getClosure(), []);
        return $this->closure->getClosure()->call($this, ...$parameters);
    }
}
