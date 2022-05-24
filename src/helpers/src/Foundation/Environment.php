<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Helpers\Foundation;

use Hyperf\Macroable\Macroable;
use Hyperf\Utils\Str;

class Environment
{
    use Macroable;

    /**
     * @var string
     */
    protected $env;

    public function __construct()
    {
        $this->env = env('APP_ENV');
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
     *
     * @return bool
     */
    public function isLocal()
    {
        return $this->environment('local');
    }

    /**
     * Determine if the application is in the dev environment.
     *
     * @return bool
     */
    public function isDev()
    {
        return $this->environment('dev');
    }

    /**
     * Determine if the application is in the develop environment.
     *
     * @return bool
     */
    public function isDevelop()
    {
        return $this->environment('develop');
    }

    /**
     * Determine if the application is in the production environment.
     *
     * @return bool
     */
    public function isProduction()
    {
        return $this->environment('production');
    }

    /**
     * Determine if the application is in the production environment.
     *
     * @return bool
     */
    public function isOnline()
    {
        return $this->environment('online');
    }
}
