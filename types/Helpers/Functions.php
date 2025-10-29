<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use Carbon\Carbon;
use FriendsOfHyperf\Support\Environment;
use Hyperf\Contract\SessionInterface;
use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\HttpMessage\Cookie\CookieJarInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Support\Fluent;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\Contract\ValidatorInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

use function FriendsOfHyperf\Helpers\app;
use function FriendsOfHyperf\Helpers\base_path;
use function FriendsOfHyperf\Helpers\blank;
use function FriendsOfHyperf\Helpers\cache;
use function FriendsOfHyperf\Helpers\class_namespace;
use function FriendsOfHyperf\Helpers\cookie;
use function FriendsOfHyperf\Helpers\di;
use function FriendsOfHyperf\Helpers\dispatch;
use function FriendsOfHyperf\Helpers\enum_value;
use function FriendsOfHyperf\Helpers\environment;
use function FriendsOfHyperf\Helpers\event;
use function FriendsOfHyperf\Helpers\filled;
use function FriendsOfHyperf\Helpers\fluent;
use function FriendsOfHyperf\Helpers\get_client_ip;
use function FriendsOfHyperf\Helpers\info;
use function FriendsOfHyperf\Helpers\literal;
use function FriendsOfHyperf\Helpers\logger;
use function FriendsOfHyperf\Helpers\logs;
use function FriendsOfHyperf\Helpers\now;
use function FriendsOfHyperf\Helpers\object_get;
use function FriendsOfHyperf\Helpers\preg_replace_array;
use function FriendsOfHyperf\Helpers\request;
use function FriendsOfHyperf\Helpers\rescue;
use function FriendsOfHyperf\Helpers\resolve;
use function FriendsOfHyperf\Helpers\response;
use function FriendsOfHyperf\Helpers\session;
use function FriendsOfHyperf\Helpers\throw_if;
use function FriendsOfHyperf\Helpers\throw_unless;
use function FriendsOfHyperf\Helpers\today;
use function FriendsOfHyperf\Helpers\transform;
use function FriendsOfHyperf\Helpers\validator;
use function FriendsOfHyperf\Helpers\when;
use function PHPStan\Testing\assertType;

// app() tests
assertType('mixed', app());
assertType('Hyperf\Support\Fluent', app(Fluent::class));
assertType('Closure', app(fn() => 'test'));

// base_path() tests
assertType('string', base_path());
assertType('string', base_path('foo/bar'));

// blank() tests
assertType('bool', blank(null));
assertType('bool', blank(''));
assertType('bool', blank('test'));
assertType('bool', blank([]));

// cache() tests
assertType('Psr\SimpleCache\CacheInterface', cache());
assertType('mixed', cache('key'));

// class_namespace() tests
assertType('string', class_namespace(Fluent::class));
assertType('string', class_namespace(new Fluent()));

// di() tests
assertType('Psr\Container\ContainerInterface', di());
assertType('Hyperf\Support\Fluent', di(Fluent::class));

// enum_value() tests
assertType('mixed', enum_value('test'));
assertType('string', enum_value('test', 'default'));

// event() tests
$testEvent = new class() {};
assertType('class@anonymous*', event($testEvent));

// filled() tests
assertType('bool', filled(null));
assertType('bool', filled('test'));

// fluent() tests
assertType('Hyperf\Support\Fluent', fluent([]));
assertType('Hyperf\Support\Fluent', fluent(['key' => 'value']));

// get_client_ip() tests
assertType('string', get_client_ip());

// literal() tests
assertType('stdClass', literal());
assertType('stdClass', literal(key: 'value'));

// logger() tests
assertType('Psr\Log\LoggerInterface', logger());
assertType('mixed', logger('test message'));

// logs() tests
assertType('Psr\Log\LoggerInterface', logs());
assertType('Psr\Log\LoggerInterface', logs('custom'));

// object_get() tests
$obj = (object)['key' => 'value'];
assertType('stdClass', object_get($obj));
assertType('mixed', object_get($obj, 'key'));

// preg_replace_array() tests
assertType('string', preg_replace_array('/test/', ['replacement'], 'test string'));

// request() tests
assertType('Hyperf\HttpServer\Contract\RequestInterface', request());
assertType('mixed', request('key'));
assertType('array', request(['key1', 'key2']));

// rescue() tests
assertType('string', rescue(fn() => 'result'));
assertType('string', rescue(fn() => throw new \Exception(), 'fallback'));

// resolve() tests
assertType('Hyperf\Support\Fluent', resolve(Fluent::class));
assertType('Closure', resolve(fn() => 'test'));

// response() tests
assertType('Psr\Http\Message\ResponseInterface', response());
assertType('Psr\Http\Message\ResponseInterface', response('content'));

// throw_if() tests
assertType('bool', throw_if(false, 'Exception'));

// throw_unless() tests
assertType('bool', throw_unless(true, 'Exception'));

// transform() tests
assertType('string', transform('value', fn($v) => $v));
assertType('mixed', transform(null, fn($v) => $v));

// validator() tests
assertType('Hyperf\Validation\Contract\ValidatorFactoryInterface', validator());
assertType('Hyperf\Contract\ValidatorInterface', validator([], []));

// when() tests
assertType('mixed', when(true, 'value'));
assertType('mixed', when(false, 'value', 'default'));

// cookie() tests
assertType('Hyperf\HttpMessage\Cookie\CookieJarInterface', cookie());
assertType('Hyperf\HttpMessage\Cookie\Cookie', cookie('name', 'value'));

// dispatch() tests - returns bool
// Note: dispatch() has complex return types based on job type, testing the common case
assertType('bool', dispatch(new class implements \Hyperf\AsyncQueue\JobInterface {
    public function handle() {}
}));

// environment() tests
assertType('FriendsOfHyperf\Support\Environment|bool', environment());
assertType('bool', environment('production'));

// info() tests
assertType('mixed', info('message'));

// now() tests
assertType('Carbon\Carbon', now());
assertType('Carbon\Carbon', now('UTC'));

// session() tests
assertType('Hyperf\Contract\SessionInterface', session());
assertType('mixed', session('key'));

// today() tests
assertType('Carbon\Carbon', today());
assertType('Carbon\Carbon', today('UTC'));
