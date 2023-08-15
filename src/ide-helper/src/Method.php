<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\IdeHelper;

use Barryvdh\Reflection\DocBlock;
use Barryvdh\Reflection\DocBlock\Context;
use Barryvdh\Reflection\DocBlock\Serializer as DocBlockSerializer;
use Barryvdh\Reflection\DocBlock\Tag;
use Barryvdh\Reflection\DocBlock\Tag\ParamTag;
use Barryvdh\Reflection\DocBlock\Tag\ReturnTag;
use ReflectionClass;
use ReflectionException;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use Throwable;

class Method
{
    protected DocBlock $phpdoc;

    protected string $output = '';

    protected string $declaringClassName;

    protected string $name;

    protected string $namespace;

    protected array $params = [];

    protected array $params_with_default = [];

    protected string $real_name;

    protected ?string $return = null;

    protected string $root;

    public function __construct(protected ReflectionFunctionAbstract|ReflectionMethod $method, string $alias, ReflectionClass $class, ?string $methodName = null, protected array $interfaces = [])
    {
        $this->name = $methodName ?: $method->name;
        $this->real_name = $method->isClosure() ? $this->name : $method->name;
        $this->initClassDefinedProperties($method, $class);

        // Reference the 'real' function in the declaring class
        $this->root = '\\' . ltrim($class->getName(), '\\');

        // Create a DocBlock and serializer instance
        $this->initPhpDoc($method);

        // Normalize the description and inherit the docs from parents/interfaces
        try {
            $this->normalizeParams($this->phpdoc);
            $this->normalizeReturn($this->phpdoc);
            $this->normalizeDescription($this->phpdoc);
        } catch (Throwable $e) {
        }

        // Get the parameters, including formatted default values
        $this->getParameters($method);

        // Make the method static
        $this->phpdoc->appendTag(Tag::createInstance('@static', $this->phpdoc));
    }

    /**
     * Get the class wherein the function resides.
     */
    public function getDeclaringClass(): string
    {
        return $this->declaringClassName;
    }

    /**
     * Return the class from which this function would be called.
     */
    public function getRoot(): string
    {
        return $this->root;
    }

    public function isInstanceCall(): bool
    {
        return ! ($this->method->isClosure() || $this->method->isStatic());
    }

    public function getRootMethodCall(): string
    {
        if ($this->isInstanceCall()) {
            return "\$instance->{$this->getRealName()}({$this->getParams()})";
        }
        return "{$this->getRoot()}::{$this->getRealName()}({$this->getParams()})";
    }

    /**
     * Get the docblock for this method.
     */
    public function getDocComment(string $prefix = "\t\t"): string
    {
        $serializer = new DocBlockSerializer(1, $prefix);
        return $serializer->getDocComment($this->phpdoc);
    }

    /**
     * Get the method name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the real method name.
     */
    public function getRealName(): string
    {
        return $this->real_name;
    }

    /**
     * Get the parameters for this method.
     *
     * @param bool $implode Wether to implode the array or not
     */
    public function getParams(bool $implode = true): string
    {
        return $implode ? implode(', ', $this->params) : $this->params;
    }

    /**
     * Get the parameters for this method including default values.
     *
     * @param bool $implode Wether to implode the array or not
     */
    public function getParamsWithDefault(bool $implode = true): string
    {
        return $implode ? implode(', ', $this->params_with_default) : $this->params_with_default;
    }

    /**
     * Should the function return a value?
     */
    public function shouldReturn(): bool
    {
        if ($this->return !== 'void' && $this->method->name !== '__construct') {
            return true;
        }

        return false;
    }

    /**
     * Get the parameters and format them correctly.
     *
     * @param ReflectionMethod $method
     * @throws ReflectionException
     */
    public function getParameters($method)
    {
        // Loop through the default values for parameters, and make the correct output string
        $params = [];
        $paramsWithDefault = [];

        foreach ($method->getParameters() as $param) {
            $paramStr = $param->isVariadic() ? '...$' . $param->getName() : '$' . $param->getName();
            $params[] = $paramStr;

            if ($param->isOptional() && ! $param->isVariadic()) {
                $default = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
                if (is_bool($default)) {
                    $default = $default ? 'true' : 'false';
                } elseif (is_array($default)) {
                    $default = '[]';
                } elseif (is_null($default)) {
                    $default = 'null';
                } elseif (is_int($default)) {
                    // $default = $default;
                } elseif (is_resource($default)) {
                    // skip to not fail
                } else {
                    $default = "'" . trim($default) . "'";
                }
                $paramStr .= " = {$default}";
            }

            $paramsWithDefault[] = $paramStr;
        }

        $this->params = $params;
        $this->params_with_default = $paramsWithDefault;
    }

    /**
     * @param ReflectionMethod $method
     */
    protected function initPhpDoc($method): void
    {
        $this->phpdoc = new DocBlock($method, new Context($this->namespace));
    }

    /**
     * @param ReflectionMethod $method
     */
    protected function initClassDefinedProperties($method, ReflectionClass $class): void
    {
        $declaringClass = $method->getDeclaringClass();
        $this->namespace = $declaringClass->getNamespaceName();
        $this->declaringClassName = '\\' . ltrim($declaringClass->name, '\\');
    }

    /**
     * Get the description and get the inherited docs.
     */
    protected function normalizeDescription(DocBlock $phpdoc): void
    {
        // Get the short + long description from the DocBlock
        $description = $phpdoc->getText();

        // Loop through parents/interfaces, to fill in {@inheritdoc}
        if (strpos($description, '{@inheritdoc}') !== false) {
            $inheritdoc = $this->getInheritDoc($this->method);
            $inheritDescription = $inheritdoc->getText();

            $description = str_replace('{@inheritdoc}', $inheritDescription, $description);
            $phpdoc->setText($description);

            $this->normalizeParams($inheritdoc);
            $this->normalizeReturn($inheritdoc);

            // Add the tags that are inherited
            $inheritTags = $inheritdoc->getTags();
            if ($inheritTags) {
                /** @var Tag $tag */
                foreach ($inheritTags as $tag) {
                    $tag->setDocBlock();
                    $phpdoc->appendTag($tag);
                }
            }
        }
    }

    /**
     * Normalize the parameters.
     */
    protected function normalizeParams(DocBlock $phpdoc): void
    {
        // Get the return type and adjust them for better autocomplete
        $paramTags = $phpdoc->getTagsByName('param');
        if ($paramTags) {
            /** @var ParamTag $tag */
            foreach ($paramTags as $tag) {
                // Convert the keywords
                $content = $this->convertKeywords($tag->getContent());
                $tag->setContent($content);

                // Get the expanded type and re-set the content
                $content = $tag->getType() . ' ' . $tag->getVariableName() . ' ' . $tag->getDescription();
                $tag->setContent(trim($content));
            }
        }
    }

    /**
     * Normalize the return tag (make full namespace, replace interfaces).
     */
    protected function normalizeReturn(DocBlock $phpdoc): void
    {
        // Get the return type and adjust them for better autocomplete
        $returnTags = $phpdoc->getTagsByName('return');
        if ($returnTags) {
            /** @var ReturnTag $tag */
            $tag = reset($returnTags);
            // Get the expanded type
            $returnValue = $tag->getType();

            // Replace the interfaces
            foreach ($this->interfaces as $interface => $real) {
                $returnValue = str_replace($interface, $real, $returnValue);
            }

            // Set the changed content
            $tag->setContent($returnValue . ' ' . $tag->getDescription());
            $this->return = $returnValue;

            if ($tag->getType() === '$this') {
                $tag->setType($this->root);
            }
        } else {
            $this->return = null;
        }
    }

    /**
     * Convert keywords that are incorrect.
     */
    protected function convertKeywords(string $string): string
    {
        $string = str_replace('\Closure', 'Closure', $string);
        $string = str_replace('Closure', '\Closure', $string);
        return str_replace('dynamic', 'mixed', $string);
    }

    /**
     * @param ReflectionMethod $reflectionMethod
     * @return DocBlock
     * @throws ReflectionException
     */
    protected function getInheritDoc($reflectionMethod)
    {
        $parentClass = $reflectionMethod->getDeclaringClass()->getParentClass();

        // Get either a parent or the interface
        if ($parentClass) {
            $method = $parentClass->getMethod($reflectionMethod->getName());
        } else {
            $method = $reflectionMethod->getPrototype();
        }

        if ($method) {
            $namespace = $method->getDeclaringClass()->getNamespaceName();
            $phpdoc = new DocBlock($method, new Context($namespace));

            if (strpos($phpdoc->getText(), '{@inheritdoc}') !== false) {
                // Not at the end yet, try another parent/interface..
                return $this->getInheritDoc($method);
            }
            return $phpdoc;
        }
    }
}
