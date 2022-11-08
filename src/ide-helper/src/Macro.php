<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\IdeHelper;

use Barryvdh\Reflection\DocBlock;
use Barryvdh\Reflection\DocBlock\Tag;
use Hyperf\Database\Model\Builder as EloquentBuilder;
use Hyperf\Utils\Collection;
use ReflectionClass;
use ReflectionFunctionAbstract;
use ReflectionMethod;

class Macro extends Method
{
    /**
     * Macro constructor.
     */
    public function __construct(ReflectionFunctionAbstract $method, string $alias, ReflectionClass $class, ?string $methodName = null, array $interfaces = [])
    {
        parent::__construct($method, $alias, $class, $methodName, $interfaces);
    }

    /**
     * @param ReflectionFunctionAbstract $method
     */
    protected function initPhpDoc($method): void
    {
        $this->phpdoc = new DocBlock($method);

        $this->addLocationToPhpDoc();

        // Add macro parameters if they are missed in original docblock
        if (! $this->phpdoc->hasTag('param')) {
            foreach ($method->getParameters() as $parameter) {
                $type = $parameter->hasType() ? $parameter->getType()->getName() : 'mixed';
                $type .= $parameter->hasType() && $parameter->getType()->allowsNull() ? '|null' : '';

                $name = $parameter->isVariadic() ? '...' : '';
                $name .= '$' . $parameter->getName();

                $this->phpdoc->appendTag(Tag::createInstance("@param {$type} {$name}"));
            }
        }

        // Add macro return type if it missed in original docblock
        if ($method->hasReturnType() && ! $this->phpdoc->hasTag('return')) {
            $builder = EloquentBuilder::class;
            $return = $method->getReturnType();

            $type = $return->getName();
            $type .= $this->root === "\\{$builder}" && $return->getName() === $builder ? '|static' : '';
            $type .= $return->allowsNull() ? '|null' : '';

            $this->phpdoc->appendTag(Tag::createInstance("@return {$type}"));
        }
    }

    protected function addLocationToPhpDoc(): void
    {
        $enclosingClass = $this->method->getClosureScopeClass();

        /** @var ReflectionMethod $enclosingMethod */
        $enclosingMethod = Collection::make($enclosingClass->getMethods())
            ->first(function (ReflectionMethod $method) {
                return $method->getStartLine() <= $this->method->getStartLine()
                    && $method->getEndLine() >= $this->method->getEndLine();
            });

        if ($enclosingMethod) {
            $this->phpdoc->appendTag(Tag::createInstance(
                '@see \\' . $enclosingClass->getName() . '::' . $enclosingMethod->getName() . '()'
            ));
        }
    }

    /**
     * @param ReflectionFunctionAbstract $method
     */
    protected function initClassDefinedProperties($method, ReflectionClass $class): void
    {
        $this->namespace = $class->getNamespaceName();
        $this->declaringClassName = '\\' . ltrim($class->name, '\\');
    }
}
