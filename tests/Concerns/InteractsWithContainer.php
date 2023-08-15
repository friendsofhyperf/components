<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Concerns;

use Closure;
use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSourceFactory;
use Mockery;
use Psr\Container\ContainerInterface;

trait InteractsWithContainer
{
    /**
     * @var \Hyperf\Di\Container|null
     */
    protected ?ContainerInterface $container = null;

    /**
     * Register an instance of an object in the container.
     *
     * @param string $abstract
     * @param object $instance
     * @return object
     */
    protected function swap($abstract, $instance)
    {
        return $this->instance($abstract, $instance);
    }

    /**
     * Register an instance of an object in the container.
     *
     * @param string $abstract
     * @param object $instance
     * @return object
     */
    protected function instance($abstract, $instance)
    {
        $this->container?->set($abstract, $instance);

        return $instance;
    }

    /**
     * Mock an instance of an object in the container.
     *
     * @param string $abstract
     * @return \Mockery\MockInterface
     */
    protected function mock($abstract, Closure $mock = null)
    {
        return $this->instance($abstract, Mockery::mock(...array_filter(func_get_args())));
    }

    /**
     * Mock a partial instance of an object in the container.
     *
     * @param string $abstract
     * @return \Mockery\MockInterface
     */
    protected function partialMock($abstract, Closure $mock = null)
    {
        return $this->instance($abstract, Mockery::mock(...array_filter(func_get_args()))->makePartial());
    }

    /**
     * Spy an instance of an object in the container.
     *
     * @param string $abstract
     * @return \Mockery\MockInterface
     */
    protected function spy($abstract, Closure $mock = null)
    {
        return $this->instance($abstract, Mockery::spy(...array_filter(func_get_args())));
    }

    protected function refreshContainer(): void
    {
        $this->container = ApplicationContext::setContainer($this->createContainer());
        // $this->container->get(\Hyperf\Contract\ApplicationInterface::class);
    }

    protected function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    protected function flushContainer(): void
    {
        $this->container = null;
    }

    protected function createContainer(): ContainerInterface
    {
        defined('BASE_PATH') or define('BASE_PATH', dirname(__DIR__, 2));

        return new Container((new DefinitionSourceFactory())());
    }
}
