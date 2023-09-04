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

test('testEnv', function () {
    $_SERVER['foo'] = 'bar';
    expect(Env::get('foo'))->toBe('bar');
});

test('testEnvTrue', function () {
    $_SERVER['foo'] = 'true';
    expect(Env::get('foo'))->toBeTrue();

    $_SERVER['foo'] = '(true)';
    expect(Env::get('foo'))->toBeTrue();
});

test('testEnvFalse', function () {
    $_SERVER['foo'] = 'false';
    expect(Env::get('foo'))->toBeFalse();

    $_SERVER['foo'] = '(false)';
    expect(Env::get('foo'))->toBeFalse();
});

test('testEnvEmpty', function () {
    $_SERVER['foo'] = '';
    expect(Env::get('foo'))->toBeEmpty();

    $_SERVER['foo'] = 'empty';
    expect(Env::get('foo'))->toBeEmpty();

    $_SERVER['foo'] = '(empty)';
    expect(Env::get('foo'))->toBeEmpty();
});

test('testEnvNull', function () {
    $_SERVER['foo'] = 'null';
    expect(Env::get('foo'))->toBeNull();

    $_SERVER['foo'] = '(null)';
    expect(Env::get('foo'))->toBeNull();
});

test('testEnvDefault', function () {
    $_SERVER['foo'] = 'bar';
    expect(Env::get('foo', 'default'))->toBe('bar');

    $_SERVER['foo'] = '';
    expect(Env::get('foo', 'default'))->toBe('');

    unset($_SERVER['foo']);
    expect(Env::get('foo', 'default'))->toBe('default');

    $_SERVER['foo'] = null;
    expect(Env::get('foo', 'default'))->toBe('default');
});

test('testEnvEscapedString', function () {
    $_SERVER['foo'] = '"null"';
    expect(Env::get('foo'))->toBe('null');

    $_SERVER['foo'] = "'null'";
    expect(Env::get('foo'))->toBe('null');

    $_SERVER['foo'] = 'x"null"x'; // this should not be unquoted
    expect(Env::get('foo'))->toBe('x"null"x');
});

test('testGetFromSERVERFirst', function () {
    $_ENV['foo'] = 'From $_ENV';
    $_SERVER['foo'] = 'From $_SERVER';
    expect(Env::get('foo'))->toBe('From $_SERVER');
});

test('testRequiredEnvVariableThrowsAnExceptionWhenNotFound', function (): void {
    $this->expectExceptionObject(new RuntimeException('[required-does-not-exist] has no value'));

    Env::getOrFail('required-does-not-exist');
});

test('testRequiredEnvReturnsValue', function (): void {
    $_SERVER['required-exists'] = 'some-value';
    expect(Env::getOrFail('required-exists'))->toBe('some-value');
});
