<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Sentry;

use FriendsOfHyperf\Sentry\ConfigProvider;
use FriendsOfHyperf\Sentry\Listener\EventHandleListener;
use FriendsOfHyperf\Sentry\Listener\RequestContextLifecycleListener;
use FriendsOfHyperf\Sentry\Tracing\Listener\EventHandleListener as TracingEventHandleListener;
use FriendsOfHyperf\Tests\TestCase;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ReflectionProperty;
use Sentry\ClientBuilder;
use Sentry\Event;
use Sentry\SentrySdk;
use Sentry\State\Hub;
use Sentry\State\RuntimeContextManager;
use Sentry\Transport\Result;
use Sentry\Transport\ResultStatus;
use Sentry\Transport\TransportInterface;

use function Hyperf\Coroutine\defer;

/**
 * @internal
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class RequestContextLifecycleListenerTest extends TestCase
{
    public function testItIsRegisteredBeforeRequestEventHandlers(): void
    {
        $listeners = (new ConfigProvider())()['listeners'];

        self::assertSame(PHP_INT_MAX, $listeners[RequestContextLifecycleListener::class]);
        self::assertSame(PHP_INT_MAX - 1, $listeners[TracingEventHandleListener::class]);
        self::assertSame(PHP_INT_MAX - 2, $listeners[EventHandleListener::class]);
    }

    public function testItIsolatesRootRequestCoroutines(): void
    {
        $this->setUpRuntimeContextManager();

        $listener = new RequestContextLifecycleListener();
        $contexts = new \Swoole\Coroutine\Channel(2);

        for ($index = 0; $index < 2; ++$index) {
            \Swoole\Coroutine::create(static function () use ($contexts, $listener): void {
                $listener->process((object) []);
                $contexts->push([
                    'hub' => spl_object_id(SentrySdk::getCurrentHub()),
                    'context' => SentrySdk::getCurrentRuntimeContext()->getId(),
                ]);
            });
        }

        $first = $contexts->pop();
        $second = $contexts->pop();

        self::assertNotSame('global', $first['context']);
        self::assertNotSame('global', $second['context']);
        self::assertNotSame($first['context'], $second['context']);
        self::assertNotSame($first['hub'], $second['hub']);
    }

    public function testItDoesNotEndAPreExistingRuntimeContext(): void
    {
        $this->setUpRuntimeContextManager();

        $listener = new RequestContextLifecycleListener();
        $contexts = new \Swoole\Coroutine\Channel(1);

        \Swoole\Coroutine::create(static function () use ($contexts, $listener): void {
            SentrySdk::startContext();
            $expectedContext = SentrySdk::getCurrentRuntimeContext()->getId();

            defer(static function () use ($contexts, $expectedContext): void {
                $actualContext = SentrySdk::getCurrentRuntimeContext()->getId();
                SentrySdk::endContext();
                $contexts->push([$expectedContext, $actualContext]);
            });

            $listener->process((object) []);
        });

        [$expectedContext, $actualContext] = $contexts->pop();

        self::assertSame($expectedContext, $actualContext);
    }

    public function testItEndsTheRuntimeContextCreatedForARequest(): void
    {
        $this->setUpRuntimeContextManager();

        $listener = new RequestContextLifecycleListener();
        $contexts = new \Swoole\Coroutine\Channel(1);

        \Swoole\Coroutine::create(static function () use ($contexts, $listener): void {
            defer(static function () use ($contexts): void {
                $contexts->push(SentrySdk::getCurrentRuntimeContext()->getId());
            });

            $listener->process((object) []);
        });

        self::assertSame('global', $contexts->pop());
    }

    private function setUpRuntimeContextManager(): void
    {
        self::assertFalse(class_exists(RuntimeContextManager::class, false));
        require dirname(__DIR__, 2) . '/src/sentry/class_map/RuntimeContextManager.php';

        $transport = new class implements TransportInterface {
            public function send(Event $event): Result
            {
                return new Result(ResultStatus::success(), $event);
            }

            public function close(?int $timeout = null): Result
            {
                return new Result(ResultStatus::success());
            }
        };
        $client = ClientBuilder::create(['dsn' => null, 'transport' => $transport])->getClient();
        $baseHub = new Hub($client);

        $this->setSentrySdkProperty('currentHub', $baseHub);
        $this->setSentrySdkProperty('runtimeContextManager', new RuntimeContextManager($baseHub));
    }

    private function setSentrySdkProperty(string $property, mixed $value): void
    {
        $reflection = new ReflectionProperty(SentrySdk::class, $property);
        $reflection->setValue(null, $value);
    }
}
