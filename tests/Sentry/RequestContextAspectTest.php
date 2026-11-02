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

use FriendsOfHyperf\Sentry\Aspect\RequestContextAspect;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Coroutine\WaitGroup;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Engine\Coroutine;
use Mockery as m;
use Sentry\Breadcrumb;
use Sentry\ClientBuilder;
use Sentry\SentrySdk;
use Sentry\State\Hub;
use Sentry\State\HubInterface;
use Sentry\State\RuntimeContextManager;
use Sentry\State\Scope;

use function Hyperf\Coroutine\defer;
use function Hyperf\Support\msleep;

if (! class_exists(RuntimeContextManager::class, false)) {
    require_once dirname(__DIR__, 2) . '/src/sentry/class_map/RuntimeContextManager.php';
}

function breadcrumbCount(HubInterface $hub): int
{
    $count = 0;
    $hub->configureScope(function (Scope $scope) use (&$count) {
        $count = count((fn () => $this->breadcrumbs)->call($scope));
    });

    return $count;
}

function runInRequestAspect(callable $request): void
{
    $joinPoint = new ProceedingJoinPoint(fn () => $request(), 'Hyperf\HttpServer\Server', 'onRequest', ['keys' => []]);
    $joinPoint->pipe = fn (ProceedingJoinPoint $point) => $point->processOriginalMethod();

    (new RequestContextAspect())->process($joinPoint);
}

beforeEach(function () {
    $container = m::mock(ContainerInterface::class);
    $container->allows('make')->with(HubInterface::class, m::any())->andReturnUsing(
        fn () => new Hub(ClientBuilder::create(['default_integrations' => false])->getClient())
    );
    ApplicationContext::setContainer($container);
});

test('it targets the server entry points', function () {
    expect((new RequestContextAspect())->classes)->toBe([
        'Hyperf\HttpServer\Server::onRequest',
        'Hyperf\RpcServer\Server::onReceive',
    ]);
});

test('concurrent requests get their own hub and do not touch the global one', function () {
    $hubs = [];
    $breadcrumbs = [];
    $wg = new WaitGroup();

    for ($i = 0; $i < 5; ++$i) {
        $wg->add();
        Coroutine::create(function () use (&$hubs, &$breadcrumbs, $wg) {
            defer(fn () => $wg->done());
            runInRequestAspect(function () use (&$hubs, &$breadcrumbs) {
                $hubs[] = $hub = SentrySdk::getCurrentHub();
                $hub->addBreadcrumb(new Breadcrumb('info', 'default', 'redis', 'GET'));
                msleep(1);
                $breadcrumbs[] = breadcrumbCount(SentrySdk::getCurrentHub());
            });
        });
    }

    $wg->wait();
    $global = SentrySdk::getCurrentHub();

    expect(array_unique(array_map(spl_object_id(...), $hubs)))->toHaveCount(5)
        ->and(array_map(spl_object_id(...), $hubs))->not->toContain(spl_object_id($global))
        ->and($breadcrumbs)->toBe([1, 1, 1, 1, 1])
        ->and(breadcrumbCount($global))->toBe(0);
});

test('the context ends after the deferred work of the request', function () {
    $seen = [];
    $wg = new WaitGroup();
    $wg->add();

    Coroutine::create(function () use (&$seen, $wg) {
        defer(function () use (&$seen, $wg) {
            $seen['after'] = SentrySdk::getCurrentHub();
            $wg->done();
        });

        runInRequestAspect(function () use (&$seen) {
            $seen['request'] = SentrySdk::getCurrentHub();
            defer(function () use (&$seen) {
                $seen['deferred'] = SentrySdk::getCurrentHub();
            });
        });
    });

    $wg->wait();

    expect($seen['deferred'])->toBe($seen['request'])
        ->and($seen['after'])->not->toBe($seen['request']);
});
