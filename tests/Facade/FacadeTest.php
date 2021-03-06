<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Tests\Facade;

use FriendsOfHyperf\Cache\Cache;
use FriendsOfHyperf\Facade\Log;
use FriendsOfHyperf\Tests\TestCase;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\ApplicationContext;
use Mockery;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class FacadeTest extends TestCase
{
    public function testExample()
    {
        $this->assertTrue(true);
    }

    public function testLog()
    {
        /** @var ContainerInterface|\Mockery\LegacyMockInterface|\Mockery\MockInterface $container */
        $container = Mockery::mock(ContainerInterface::class)
            ->shouldReceive('get')
            ->with(LoggerFactory::class)
            ->andReturn(
                Mockery::mock(LoggerFactory::class)
                    ->shouldReceive('get')
                    ->with('hyperf', 'default')
                    ->andReturn(
                        Mockery::mock(\Psr\Log\LoggerInterface::class)
                            ->shouldReceive('info')
                            ->with('test')
                            ->getMock()
                    )
                    ->getMock()
            )
            ->getMock();

        ApplicationContext::setContainer($container);

        $this->assertInstanceOf(\Psr\Log\LoggerInterface::class, Log::channel('hyperf', 'default'));
        // $this->assertEmpty(Log::info('test'));
    }

    public function testCacheMacroable()
    {
        Cache::macro('test', fn () => null);

        $this->assertTrue(Cache::hasMacro('test'));
    }
}
