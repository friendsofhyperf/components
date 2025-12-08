<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Support\Backoff\ArrayBackoff;
use FriendsOfHyperf\Support\Backoff\BackoffInterface;
use FriendsOfHyperf\Support\Backoff\DecorrelatedJitterBackoff;
use FriendsOfHyperf\Support\Backoff\ExponentialBackoff;
use FriendsOfHyperf\Support\Backoff\FibonacciBackoff;
use FriendsOfHyperf\Support\Backoff\FixedBackoff;
use FriendsOfHyperf\Support\Backoff\LinearBackoff;
use FriendsOfHyperf\Support\Backoff\PoissonBackoff;

use function PHPStan\Testing\assertType;

// ArrayBackoff tests
$arrayBackoff = new ArrayBackoff([100, 200, 300]);
assertType(ArrayBackoff::class, $arrayBackoff);
assertType('int', $arrayBackoff->next());
assertType('int', $arrayBackoff->getAttempt());
assertType('int', $arrayBackoff->sleep());
$arrayBackoff->reset();
assertType('array<int>', $arrayBackoff->getDelays());
assertType('bool', $arrayBackoff->isUsingLastValue());

// ArrayBackoff static constructors
assertType(ArrayBackoff::class, ArrayBackoff::fromString('100,200,300'));
assertType(ArrayBackoff::class, ArrayBackoff::fromPattern('short'));
assertType(ArrayBackoff::class, ArrayBackoff::fromPattern('medium'));
assertType(ArrayBackoff::class, ArrayBackoff::fromPattern('long'));
assertType(ArrayBackoff::class, ArrayBackoff::fromPattern('exponential'));

// ExponentialBackoff tests
$exponentialBackoff = new ExponentialBackoff();
assertType(ExponentialBackoff::class, $exponentialBackoff);
assertType('int', $exponentialBackoff->next());
assertType('int', $exponentialBackoff->getAttempt());
assertType('int', $exponentialBackoff->sleep());
$exponentialBackoff->reset();

$exponentialBackoffCustom = new ExponentialBackoff(100, 10000, 2.0, true);
assertType(ExponentialBackoff::class, $exponentialBackoffCustom);
assertType('int', $exponentialBackoffCustom->next());

// LinearBackoff tests
$linearBackoff = new LinearBackoff();
assertType(LinearBackoff::class, $linearBackoff);
assertType('int', $linearBackoff->next());
assertType('int', $linearBackoff->getAttempt());
assertType('int', $linearBackoff->sleep());
$linearBackoff->reset();

$linearBackoffCustom = new LinearBackoff(100, 50, 2000);
assertType(LinearBackoff::class, $linearBackoffCustom);
assertType('int', $linearBackoffCustom->next());

// FixedBackoff tests
$fixedBackoff = new FixedBackoff();
assertType(FixedBackoff::class, $fixedBackoff);
assertType('int', $fixedBackoff->next());
assertType('int', $fixedBackoff->getAttempt());
assertType('int', $fixedBackoff->sleep());
$fixedBackoff->reset();

$fixedBackoffCustom = new FixedBackoff(500);
assertType(FixedBackoff::class, $fixedBackoffCustom);
assertType('int', $fixedBackoffCustom->next());

// FibonacciBackoff tests
$fibonacciBackoff = new FibonacciBackoff();
assertType(FibonacciBackoff::class, $fibonacciBackoff);
assertType('int', $fibonacciBackoff->next());
assertType('int', $fibonacciBackoff->getAttempt());
assertType('int', $fibonacciBackoff->sleep());
$fibonacciBackoff->reset();

$fibonacciBackoffCustom = new FibonacciBackoff(10000);
assertType(FibonacciBackoff::class, $fibonacciBackoffCustom);
assertType('int', $fibonacciBackoffCustom->next());

// DecorrelatedJitterBackoff tests
$decorrelatedJitterBackoff = new DecorrelatedJitterBackoff();
assertType(DecorrelatedJitterBackoff::class, $decorrelatedJitterBackoff);
assertType('int', $decorrelatedJitterBackoff->next());
assertType('int', $decorrelatedJitterBackoff->getAttempt());
assertType('int', $decorrelatedJitterBackoff->sleep());
$decorrelatedJitterBackoff->reset();

$decorrelatedJitterBackoffCustom = new DecorrelatedJitterBackoff(100, 10000, 3.0);
assertType(DecorrelatedJitterBackoff::class, $decorrelatedJitterBackoffCustom);
assertType('int', $decorrelatedJitterBackoffCustom->next());

// PoissonBackoff tests
$poissonBackoff = new PoissonBackoff();
assertType(PoissonBackoff::class, $poissonBackoff);
assertType('int', $poissonBackoff->next());
assertType('int', $poissonBackoff->getAttempt());
assertType('int', $poissonBackoff->sleep());
$poissonBackoff->reset();

$poissonBackoffCustom = new PoissonBackoff(100, 5000);
assertType(PoissonBackoff::class, $poissonBackoffCustom);
assertType('int', $poissonBackoffCustom->next());

// BackoffInterface tests - verify all implementations are compatible
assertType('FriendsOfHyperf\Support\Backoff\ArrayBackoff', $arrayBackoff);
assertType('FriendsOfHyperf\Support\Backoff\ExponentialBackoff', $exponentialBackoff);
assertType('FriendsOfHyperf\Support\Backoff\LinearBackoff', $linearBackoff);
assertType('FriendsOfHyperf\Support\Backoff\FixedBackoff', $fixedBackoff);
assertType('FriendsOfHyperf\Support\Backoff\FibonacciBackoff', $fibonacciBackoff);
assertType('FriendsOfHyperf\Support\Backoff\DecorrelatedJitterBackoff', $decorrelatedJitterBackoff);
assertType('FriendsOfHyperf\Support\Backoff\PoissonBackoff', $poissonBackoff);

// Test polymorphic usage
function useBackoff(BackoffInterface $backoff): void
{
    assertType('int', $backoff->next());
    assertType('int', $backoff->sleep());
    assertType('int', $backoff->getAttempt());
    $backoff->reset();
}

useBackoff($arrayBackoff);
useBackoff($exponentialBackoff);
useBackoff($linearBackoff);
useBackoff($fixedBackoff);
useBackoff($fibonacciBackoff);
useBackoff($decorrelatedJitterBackoff);
useBackoff($poissonBackoff);
