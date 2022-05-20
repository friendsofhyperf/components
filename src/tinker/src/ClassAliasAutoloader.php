<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/tinker.
 *
 * @link     https://github.com/friendsofhyperf/tinker
 * @document https://github.com/friendsofhyperf/tinker/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Tinker;

use Hyperf\Utils\Str;
use Psy\Shell;

class ClassAliasAutoloader
{
    /**
     * The shell instance.
     *
     * @var \Psy\Shell
     */
    protected $shell;

    /**
     * All of the discovered classes.
     *
     * @var array
     */
    protected $classes = [];

    /**
     * Path to the vendor directory.
     *
     * @var string
     */
    protected $vendorPath;

    /**
     * Explicitly included namespaces/classes.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $includedAliases;

    /**
     * Excluded namespaces/classes.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $excludedAliases;

    /**
     * Create a new alias loader instance.
     *
     * @param string $classMapPath
     */
    public function __construct(Shell $shell, $classMapPath, array $includedAliases = [], array $excludedAliases = [])
    {
        $this->shell = $shell;
        $this->vendorPath = dirname(dirname($classMapPath));
        $this->includedAliases = collect($includedAliases);
        $this->excludedAliases = collect($excludedAliases);

        $classes = require $classMapPath;

        foreach ($classes as $class => $path) {
            if (! $this->isAliasable($class, $path)) {
                continue;
            }

            $name = class_basename($class);

            if (! isset($this->classes[$name])) {
                $this->classes[$name] = $class;
            }
        }
    }

    /**
     * Handle the destruction of the instance.
     */
    public function __destruct()
    {
        $this->unregister();
    }

    /**
     * Register a new alias loader instance.
     *
     * @param string $classMapPath
     * @return static
     */
    public static function register(Shell $shell, $classMapPath, array $includedAliases = [], array $excludedAliases = [])
    {
        return tap(
            new static($shell, $classMapPath, $includedAliases, $excludedAliases),
            function ($loader) {
                spl_autoload_register([$loader, 'aliasClass']);
            }
        );
    }

    /**
     * Find the closest class by name.
     *
     * @param string $class
     */
    public function aliasClass($class)
    {
        if (Str::contains($class, '\\')) {
            return;
        }

        $fullName = $this->classes[$class] ?? false;

        if ($fullName) {
            $this->shell->writeStdout("[!] Aliasing '{$class}' to '{$fullName}' for this Tinker session.\n");

            class_alias($fullName, $class);
        }
    }

    /**
     * Unregister the alias loader instance.
     */
    public function unregister()
    {
        spl_autoload_unregister([$this, 'aliasClass']);
    }

    /**
     * Whether a class may be aliased.
     *
     * @param string $class
     * @param string $path
     */
    public function isAliasable($class, $path)
    {
        if (! Str::contains($class, '\\')) {
            return false;
        }

        if (! $this->includedAliases->filter(function ($alias) use ($class) {
            return Str::startsWith($class, $alias);
        })->isEmpty()) {
            return true;
        }

        if (Str::startsWith($path, $this->vendorPath)) {
            return false;
        }

        if (! $this->excludedAliases->filter(function ($alias) use ($class) {
            return Str::startsWith($class, $alias);
        })->isEmpty()) {
            return false;
        }

        return true;
    }
}
