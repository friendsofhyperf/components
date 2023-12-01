<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\AsyncTask;

use Closure;
use Laravel\SerializableClosure\SerializableClosure;
use ReflectionFunction;

use function Hyperf\Support\with;

class ClosureTask extends AbstractTask
{
    public string $class = 'Closure';

    public string $method;

    protected SerializableClosure $closure;

    public function __construct(Closure $closure)
    {
        $this->closure = new SerializableClosure($closure);
        $this->method = with(new ReflectionFunction($this->closure->getClosure()), function ($reflection) {
            return sprintf('%s:%s', str_replace(rtrim(BASE_PATH, '/') . '/', '', $reflection->getFileName()), $reflection->getStartLine());
        });
    }

    public function handle(): void
    {
        $this->closure->__invoke();
    }
}
