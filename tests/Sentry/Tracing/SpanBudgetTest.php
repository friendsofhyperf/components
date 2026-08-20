<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Sentry\Tracing\SpanBudget;
use Hyperf\Context\Context;
use Hyperf\Engine\Channel;
use Swoole\Coroutine;

uses()->group('sentry');

beforeEach(function () {
    Context::destroy(SpanBudget::CONTEXT_KEY);
});

test('limit of 3 allows only the first three acquisitions', function () {
    $budget = new SpanBudget(3);

    expect($budget->tryAcquire())->toBeTrue()
        ->and($budget->tryAcquire())->toBeTrue()
        ->and($budget->tryAcquire())->toBeTrue()
        ->and($budget->count())->toBe(3)
        ->and($budget->tryAcquire())->toBeFalse();
});

test('reset allows acquiring again', function () {
    $budget = new SpanBudget(3);

    $budget->tryAcquire();
    $budget->tryAcquire();
    $budget->tryAcquire();

    expect($budget->tryAcquire())->toBeFalse();

    $budget->reset();

    expect($budget->tryAcquire())->toBeTrue()
        ->and($budget->count())->toBe(1);
});

test('limit of 0 means unlimited', function () {
    $budget = new SpanBudget(0);

    for ($i = 0; $i < 100; ++$i) {
        expect($budget->tryAcquire())->toBeTrue();
    }

    expect($budget->count())->toBe(0);
});

test('counter is isolated between coroutines', function () {
    Swoole\Coroutine\run(function () {
        $budget = new SpanBudget(3);
        $channel = new Channel(2);
        $results = [];

        Coroutine::create(function () use ($budget, $channel, &$results) {
            $results['co1'] = [
                $budget->tryAcquire(),
                $budget->tryAcquire(),
                $budget->tryAcquire(),
                $budget->tryAcquire(),
                $budget->count(),
            ];
            $channel->push(true);
        });

        Coroutine::create(function () use ($budget, $channel, &$results) {
            $results['co2'] = [
                $budget->tryAcquire(),
                $budget->count(),
            ];
            $channel->push(true);
        });

        $channel->pop();
        $channel->pop();

        expect($results['co1'])->toBe([true, true, true, false, 3])
            ->and($results['co2'])->toBe([true, 1]);
    });
});
