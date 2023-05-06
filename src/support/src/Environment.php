<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Support;

use Hyperf\Macroable\Macroable;
use Hyperf\Stringable\Str;

use function Hyperf\Support\env;

class Environment
{
    use Macroable;

    public function __construct(protected ?string $env = null)
    {
        $this->env = $env ?? env('APP_ENV');
    }

    /**
     * Get or check the current application environment.
     *
     * @param array|string $environments
     * @return bool|string
     */
    public function environment(...$environments)
    {
        if (count($environments) > 0) {
            $patterns = is_array($environments[0]) ? $environments[0] : $environments;

            return Str::is($patterns, $this->env);
        }

        return $this->env;
    }

    /**
     * Determine if the application is in the local environment.
     */
    public function isLocal(): bool
    {
        return $this->environment('local');
    }

    /**
     * Determine if the application is in the dev environment.
     */
    public function isDev(): bool
    {
        return $this->environment('dev');
    }

    /**
     * Determine if the application is in the develop environment.
     */
    public function isDevelop(): bool
    {
        return $this->environment('develop');
    }

    /**
     * Determine if the application is in the production environment.
     */
    public function isProduction(): bool
    {
        return $this->environment('production');
    }

    /**
     * Determine if the application is in the production environment.
     */
    public function isOnline(): bool
    {
        return $this->environment('online');
    }
}
