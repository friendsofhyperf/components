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

use BadMethodCallException;
use Hyperf\Macroable\Macroable;
use Hyperf\Stringable\Str;

use function Hyperf\Support\env;

/**
 * @method bool isLocal()
 * @method bool isDev()
 * @method bool isDevelop()
 * @method bool isProduction()
 * @method bool isOnline()
 */
class Environment
{
    use Macroable;

    public function __construct(protected ?string $env = null)
    {
        $this->env = $env ?? env('APP_ENV');
    }

    public function __call($method, $parameters = [])
    {
        if (Str::startsWith($method, 'is')) {
            return $this->is(Str::snake(substr($method, 2)));
        }

        throw new BadMethodCallException(sprintf('Method %s::%s does not exist.', static::class, $method));
    }

    /**
     * Get the current application environment.
     */
    public function get(): ?string
    {
        return $this->env;
    }

    /**
     * check the current application environment.
     * @param string|string[] $environments
     */
    public function is(...$environments): bool
    {
        $patterns = is_array($environments[0]) ? $environments[0] : $environments;

        return Str::is($patterns, $this->env);
    }
}
