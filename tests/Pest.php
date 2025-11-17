<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use Faker\Factory;
use Faker\Generator;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Mockery as m;

uses(FriendsOfHyperf\Tests\TestCase::class)->in('*/*');
uses()->group('async-queue-closure-job')->in('AsyncQueueClosureJob');
uses()->group('cache')->in('Cache');
uses()->group('config-consul')->in('ConfigConsul');
uses()->group('elasticsearch')->in('Elasticsearch');
uses()->group('encryption')->in('Encryption');
uses()->group('facade')->in('Facade');
uses()->group('fast-paginate')->in('FastPaginate');
uses()->group('helpers')->in('Helpers');
uses()->group('http-client')->in('HttpClient');
uses()->group('macros')->in('Macros');
uses()->group('mail')->in('Mail');
uses()->group('notification')->in('Notification');
uses()->group('redis-subscriber')->in('RedisSubscriber');
uses()->group('sentry')->in('Sentry');
uses()->group('support')->in('Support');
uses()->group('tinker')->in('Tinker');
uses()->group('tcp-sender')->in('TcpSender');
uses()->group('telescope')->in('Telescope');
uses()->group('lock')->in('Lock');
uses()->group('rate-limit')->in('RateLimit');
uses()->group('validated-dto')
    ->beforeEach(function () {
        $this->subject_name = faker()->name();
        $this->name = faker()->name();
        $this->age = faker()->numberBetween(1, 100);
        $this->timezone = faker()->timezone();
        date_default_timezone_set($this->timezone);

        $this->mock(ValidatorFactoryInterface::class, function ($mock) {
            $mock->shouldReceive('make')->andReturn(m::mock(ValidatorInterface::class, function ($mock) {
                $mock->shouldReceive('fails')->andReturn(false)
                    ->shouldReceive('passes')->andReturn(true)
                    ->shouldReceive('after')->andReturn(null);
            }));
        });

        $this->mock(ConfigInterface::class, function ($mock) {
            $mock->shouldReceive('get')->with('dto')->andReturn([]);
        });
    })
    ->in('ValidatedDTO');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Returns the string "test_property".
 */
function test_property(): string
{
    return 'test_property';
}

function faker(string $locale = Factory::DEFAULT_LOCALE): Generator
{
    return Factory::create($locale);
}
