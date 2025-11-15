<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Support\Env;
use FriendsOfHyperf\Support\Environment;
use FriendsOfHyperf\Support\HtmlString;
use FriendsOfHyperf\Support\Number;
use FriendsOfHyperf\Support\Once;
use FriendsOfHyperf\Support\Onceable;
use FriendsOfHyperf\Support\RedisCommand;
use FriendsOfHyperf\Support\Sleep;
use FriendsOfHyperf\Support\Timebox;

use function FriendsOfHyperf\Support\dispatch;
use function FriendsOfHyperf\Support\once;
use function PHPStan\Testing\assertType;

assertType(Once::class, Once::instance());

assertType('string', once(static fn (): string => 'value'));

assertType(Onceable::class . '|null', Onceable::tryFromTrace(debug_backtrace(), static fn (): int => 1));

$timebox = new Timebox();

assertType('int', $timebox->call(static fn (Timebox $box): int => 42, 500));

assertType(Timebox::class, $timebox->returnEarly());
assertType(Timebox::class, $timebox->dontReturnEarly());

assertType(Sleep::class, Sleep::for(1));
assertType(Sleep::class, Sleep::sleep(1));
assertType(Sleep::class, Sleep::usleep(1));
assertType(Sleep::class, Sleep::until(1));

$sleep = Sleep::for(1)->and(1)->seconds();
assertType(Sleep::class, $sleep);

assertType('int', Number::parseInt('100'));
assertType('float', Number::parseFloat('100.5'));
assertType('float|int', Number::parse('100'));
assertType('string|false', Number::format(1000));
assertType('float|int', Number::clamp(5, 1, 10));

$html = new HtmlString('<p>Hello</p>');
assertType('string', $html->toHtml());
assertType('bool', $html->isEmpty());
assertType('bool', $html->isNotEmpty());

$environment = new Environment('production');
assertType('string|null', $environment->get());
assertType('bool', $environment->is('production'));

assertType('Dotenv\Repository\RepositoryInterface', Env::getRepository());

$command = new RedisCommand('SET', ['key', 'value']);
assertType('string', (string) $command);

assertType(
    'FriendsOfHyperf\Support\Bus\PendingAsyncQueueDispatch',
    dispatch(new class implements Hyperf\AsyncQueue\JobInterface {
        public function handle(): void
        {
        }

        public function fail(Throwable $e): void
        {
        }

        public function getMaxAttempts(): int
        {
            return 0;
        }

        public function setMaxAttempts(int $maxAttempts): static
        {
            return $this;
        }
    })
);

assertType(
    'FriendsOfHyperf\Support\Bus\PendingAsyncQueueDispatch',
    dispatch(fn () => null)
);
