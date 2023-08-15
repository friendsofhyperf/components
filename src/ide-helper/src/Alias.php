<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\IdeHelper;

use Barryvdh\Reflection\DocBlock;
use Barryvdh\Reflection\DocBlock\Context;
use Barryvdh\Reflection\DocBlock\Serializer as DocBlockSerializer;
use Barryvdh\Reflection\DocBlock\Tag\MethodTag;
use Closure;
use Exception;
use PDOException;
use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use Stringable;

use function Hyperf\Collection\collect;

class Alias
{
    protected string $facade;

    protected ?string $extends;

    protected string $classType = 'class';

    protected string $short;

    protected string $namespace = '__root';

    protected string $root = '';

    protected array $classes = [];

    protected array $methods = [];

    protected bool $valid = false;

    protected ?DocBlock $phpdoc = null;

    protected array $usedMethods = [];

    protected ?string $extendsClass;

    protected ?string $extendsNamespace;

    /**
     * @throws \ReflectionException
     */
    public function __construct(protected string $alias, string $facade, protected array $magicMethods = [], protected array $interfaces = [])
    {
        // Make the class absolute
        $facade = '\\' . ltrim($facade, '\\');
        $this->facade = $facade;

        $this->detectRoot();

        if (! $this->isTrait() && $this->root) {
            $this->valid = true;
        } else {
            return;
        }

        $this->addClass($this->root);
        $this->detectFake();
        $this->detectNamespace();
        $this->detectClassType();
        $this->detectExtendsNamespace();

        if (! empty($this->namespace)) {
            // Create a DocBlock and serializer instance
            $this->phpdoc = new DocBlock(new ReflectionClass($alias), new Context($this->namespace));
        }

        if ($facade === '\Hyperf\Database\Model\Model') {
            $this->usedMethods = ['decrement', 'increment'];
        }
    }

    /**
     * Add one or more classes to analyze.
     */
    public function addClass(array|string $classes): void
    {
        $classes = (array) $classes;
        foreach ($classes as $class) {
            if (class_exists($class) || interface_exists($class)) {
                $this->classes[] = $class;
            } else {
                echo "Class not exists: {$class}\r\n";
            }
        }
    }

    /**
     * Check if this class is valid to process.
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * Get the class type, 'interface' or 'class'.
     */
    public function getClassType(): string
    {
        return $this->classType;
    }

    /**
     * Get the class which this alias extends.
     */
    public function getExtends(): ?string
    {
        return $this->extends;
    }

    /**
     * Get the class short name which this alias extends.
     */
    public function getExtendsClass(): ?string
    {
        return $this->extendsClass;
    }

    /**
     * Get the namespace of the class which this alias extends.
     */
    public function getExtendsNamespace(): ?string
    {
        return $this->extendsNamespace;
    }

    /**
     * Get the Alias by which this class is called.
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * Return the short name (without namespace).
     */
    public function getShortName(): string
    {
        return $this->short;
    }

    /**
     * Get the namespace from the alias.
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * Get the methods found by this Alias.
     *
     * @return array|Method[]
     */
    public function getMethods(): array
    {
        if (count($this->methods) > 0) {
            return $this->methods;
        }

        $this->addMagicMethods();
        $this->detectMethods();

        return $this->methods;
    }

    /**
     * Get the docblock for this alias.
     *
     * @param string $prefix
     * @return mixed
     */
    public function getDocComment($prefix = "\t\t"): string
    {
        $serializer = new DocBlockSerializer(1, $prefix);

        if ($this->phpdoc) {
            // if a class doesn't expose any DocBlock tags
            // we can perform reflection on the class and
            // add in the original class DocBlock
            if (count($this->phpdoc->getTags()) === 0) {
                $class = new ReflectionClass($this->root);
                $this->phpdoc = new DocBlock($class->getDocComment());
            }

            $this->removeDuplicateMethodsFromPhpDoc();

            return $serializer->getDocComment($this->phpdoc);
        }

        return '';
    }

    /**
     * Detect class returned by ::fake().
     */
    protected function detectFake()
    {
        $facade = $this->facade;

        if (! method_exists($facade, 'fake')) {
            return;
        }

        $real = $facade::getFacadeRoot();

        try {
            $facade::fake();
            $fake = $facade::getFacadeRoot();
            if ($fake !== $real) {
                $this->addClass(get_class($fake));
            }
        } finally {
            $facade::swap($real);
        }
    }

    /**
     * Detect the namespace.
     */
    protected function detectNamespace(): void
    {
        if (strpos($this->alias, '\\')) {
            $nsParts = explode('\\', $this->alias);
            $this->short = array_pop($nsParts);
            $this->namespace = implode('\\', $nsParts);
        } else {
            $this->short = $this->alias;
        }
    }

    /**
     * Detect the extends namespace.
     */
    protected function detectExtendsNamespace(): void
    {
        if (strpos($this->extends, '\\') !== false) {
            $nsParts = explode('\\', $this->extends);
            $this->extendsClass = array_pop($nsParts);
            $this->extendsNamespace = implode('\\', $nsParts);
        }
    }

    /**
     * Detect the class type.
     */
    protected function detectClassType(): void
    {
        // Some classes extend the facade
        if (interface_exists($this->facade)) {
            $this->classType = 'interface';
            $this->extends = $this->facade;
        } else {
            $this->classType = 'class';
            if (class_exists($this->facade)) {
                $this->extends = $this->facade;
            }
        }
    }

    /**
     * Get the real root of a facade.
     */
    protected function detectRoot(): void
    {
        $facade = $this->facade;

        try {
            // If possible, get the facade root
            if (method_exists($facade, 'getFacadeRoot')) {
                $root = get_class($facade::getFacadeRoot());
            } else {
                $root = $facade;
            }

            // If it doesn't exist, skip it
            if (! class_exists($root) && ! interface_exists($root)) {
                return;
            }

            $this->root = $root;

            // When the database connection is not set, some classes will be skipped
        } catch (PDOException $e) {
            $this->error(
                'PDOException: ' . $e->getMessage() .
                "\nPlease configure your database connection correctly, or use the sqlite memory driver (-M)." .
                " Skipping {$facade}."
            );
        } catch (Exception $e) {
            $this->error('Exception: ' . $e->getMessage() . "\nSkipping {$facade}.");
        }
    }

    /**
     * Detect if this class is a trait or not.
     */
    protected function isTrait(): bool
    {
        // Check if the facade is not a Trait
        if (function_exists('trait_exists') && trait_exists($this->facade)) {
            return true;
        }

        return false;
    }

    /**
     * Add magic methods, as defined in the configuration files.
     */
    protected function addMagicMethods(): void
    {
        foreach ($this->magicMethods as $magic => $real) {
            [$className, $name] = explode('::', $real);
            if ((! class_exists($className) && ! interface_exists($className)) || ! method_exists($className, $name)) {
                continue;
            }
            $method = new ReflectionMethod($className, $name);
            $class = new ReflectionClass($className);

            if (! in_array($magic, $this->usedMethods)) {
                if ($class !== $this->root) {
                    $this->methods[] = new Method($method, $this->alias, $class, $magic, $this->interfaces);
                }
                $this->usedMethods[] = $magic;
            }
        }
    }

    /**
     * Get the methods for one or multiple classes.
     */
    protected function detectMethods(): void
    {
        foreach ($this->classes as $class) {
            $reflection = new ReflectionClass($class);

            $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
            if ($methods) {
                foreach ($methods as $method) {
                    if (! in_array($method->name, $this->usedMethods)) {
                        // Only add the methods to the output when the root is not the same as the class.
                        // And don't add the __*() methods
                        if ($this->extends !== $class && substr($method->name, 0, 2) !== '__') {
                            $this->methods[] = new Method(
                                $method,
                                $this->alias,
                                $reflection,
                                $method->name,
                                $this->interfaces
                            );
                        }
                        $this->usedMethods[] = $method->name;
                    }
                }
            }

            // Check if the class is macroable
            $traits = collect($reflection->getTraitNames());
            if ($traits->contains('Hyperf\Macroable\Macroable')) {
                $properties = $reflection->getStaticProperties();
                $macros = isset($properties['macros']) ? $properties['macros'] : [];
                foreach ($macros as $macro_name => $macro_func) {
                    if (! in_array($macro_name, $this->usedMethods)) {
                        // Add macros
                        $this->methods[] = new Macro(
                            $this->getMacroFunction($macro_func),
                            $this->alias,
                            $reflection,
                            $macro_name,
                            $this->interfaces
                        );
                        $this->usedMethods[] = $macro_name;
                    }
                }
            }
        }
    }

    /**
     * @param mixed $macroFunc
     *
     * @throws ReflectionException
     */
    protected function getMacroFunction($macroFunc): ReflectionFunctionAbstract
    {
        if (is_array($macroFunc) && is_callable($macroFunc)) {
            return new ReflectionMethod($macroFunc[0], $macroFunc[1]);
        }

        if (is_object($macroFunc) && is_callable($macroFunc) && ! $macroFunc instanceof Closure) {
            return new ReflectionMethod($macroFunc, '__invoke');
        }

        return new ReflectionFunction($macroFunc);
    }

    /**
     * Removes method tags from the doc comment that already appear as functions inside the class.
     * This prevents duplicate function errors in the IDE.
     */
    protected function removeDuplicateMethodsFromPhpDoc(): void
    {
        $methodNames = array_map(function (Method $method) {
            return $method->getName();
        }, $this->getMethods());

        foreach ($this->phpdoc->getTags() as $tag) {
            if ($tag instanceof MethodTag && in_array($tag->getMethodName(), $methodNames)) {
                $this->phpdoc->deleteTag($tag);
            }
        }
    }

    /**
     * Output an error.
     *
     * @param string|Stringable $string
     */
    protected function error($string)
    {
        echo $string . "\r\n";
    }
}
