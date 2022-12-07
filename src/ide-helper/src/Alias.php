<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
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
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;

class Alias
{
    protected $alias;

    protected $facade;

    protected $extends;

    protected $classType = 'class';

    protected $short;

    protected $namespace = '__root';

    protected $root;

    protected $classes = [];

    protected $methods = [];

    protected $valid = false;

    protected $interfaces = [];

    protected $phpdoc;

    protected $magicMethods;

    protected $usedMethods = [];

    protected $extendsClass;

    protected $extendsNamespace;

    /**
     * @param string $alias
     * @param string $facade
     * @param array $magicMethods
     * @param array $interfaces
     * @throws ReflectionException
     */
    public function __construct($alias, $facade, $magicMethods = [], $interfaces = [])
    {
        $this->alias = $alias;
        $this->magicMethods = $magicMethods;
        $this->interfaces = $interfaces;

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
     *
     * @param array|string $classes
     */
    public function addClass($classes)
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
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * Get the classtype, 'interface' or 'class'.
     *
     * @return string
     */
    public function getClasstype()
    {
        return $this->classType;
    }

    /**
     * Get the class which this alias extends.
     *
     * @return null|string
     */
    public function getExtends()
    {
        return $this->extends;
    }

    /**
     * Get the class short name which this alias extends.
     *
     * @return null|string
     */
    public function getExtendsClass()
    {
        return $this->extendsClass;
    }

    /**
     * Get the namespace of the class which this alias extends.
     *
     * @return null|string
     */
    public function getExtendsNamespace()
    {
        return $this->extendsNamespace;
    }

    /**
     * Get the Alias by which this class is called.
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Return the short name (without namespace).
     */
    public function getShortName()
    {
        return $this->short;
    }

    /**
     * Get the namespace from the alias.
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Get the methods found by this Alias.
     *
     * @return array|Method[]
     */
    public function getMethods()
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
    public function getDocComment($prefix = "\t\t")
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
    protected function detectNamespace()
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
    protected function detectExtendsNamespace()
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
    protected function detectClassType()
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
    protected function detectRoot()
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
     *
     * @return bool
     */
    protected function isTrait()
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
    protected function addMagicMethods()
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
    protected function detectMethods()
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
            if ($traits->contains('Hyperf\Utils\Traits\Macroable')) {
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
     * @param mixed $macro_func
     * @return ReflectionFunctionAbstract
     * @throws ReflectionException
     */
    protected function getMacroFunction($macro_func)
    {
        if (is_array($macro_func) && is_callable($macro_func)) {
            return new ReflectionMethod($macro_func[0], $macro_func[1]);
        }

        if (is_object($macro_func) && is_callable($macro_func) && ! $macro_func instanceof Closure) {
            return new ReflectionMethod($macro_func, '__invoke');
        }

        return new ReflectionFunction($macro_func);
    }

    /**
     * Removes method tags from the doc comment that already appear as functions inside the class.
     * This prevents duplicate function errors in the IDE.
     */
    protected function removeDuplicateMethodsFromPhpDoc()
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
     * @param string $string
     */
    protected function error($string)
    {
        echo $string . "\r\n";
    }
}
