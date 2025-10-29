<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use Hyperf\Context\Context;
use Hyperf\HttpServer\Request;

use function PHPStan\Testing\assertType;

// Create a mock request for testing
Context::set(Psr\Http\Message\ServerRequestInterface::class, new Hyperf\HttpMessage\Server\Request('GET', '/'));
$request = new Request();

// Request::boolean() tests
assertType('bool', $request->boolean('is_active'));
assertType('bool', $request->boolean('flag', false));

// Request::collect() tests
assertType('mixed', $request->collect());
assertType('Hyperf\Collection\Collection', $request->collect('key'));
assertType('Hyperf\Collection\Collection', $request->collect(['key1', 'key2']));

// Request::date() tests
assertType('Carbon\Carbon|null', $request->date('created_at'));
assertType('Carbon\Carbon|null', $request->date('updated_at', 'Y-m-d'));
assertType('Carbon\Carbon|null', $request->date('deleted_at', 'Y-m-d H:i:s', 'UTC'));

// Request::enum() tests
assertType('mixed', $request->enum('status', BackedEnum::class));

// Request::exists() tests
assertType('bool', $request->exists('key'));

// Request::filled() tests
assertType('bool', $request->filled('name'));
assertType('bool', $request->filled(['name', 'email']));

// Request::float() tests
assertType('float', $request->float('price'));
assertType('float', $request->float('amount', 0.0));

// Request::fluent() tests
assertType('Hyperf\Support\Fluent', $request->fluent());
assertType('Hyperf\Support\Fluent', $request->fluent('key'));
assertType('Hyperf\Support\Fluent', $request->fluent(['key1', 'key2']));

// Request::hasAny() tests
assertType('bool', $request->hasAny(['key1', 'key2']));
assertType('bool', $request->hasAny('key'));

// Request::host() tests
assertType('string', $request->host());

// Request::httpHost() tests
assertType('string', $request->httpHost());

// Request::integer() tests
assertType('int', $request->integer('count'));
assertType('int', $request->integer('total', 0));

// Request::isEmptyString() tests
assertType('bool', $request->isEmptyString('field'));

// Request::isJson() tests
assertType('bool', $request->isJson());

// Request::isNotFilled() tests
assertType('bool', $request->isNotFilled('field'));
assertType('bool', $request->isNotFilled(['field1', 'field2']));

// Request::keys() tests
assertType('array', $request->keys());

// Request::missing() tests
assertType('bool', $request->missing('key'));
assertType('bool', $request->missing(['key1', 'key2']));

// Request::str() tests
assertType('Hyperf\Stringable\Stringable', $request->str('name'));
assertType('Hyperf\Stringable\Stringable', $request->str('title', 'default'));

// Request::string() tests
assertType('Hyperf\Stringable\Stringable', $request->string('name'));
assertType('Hyperf\Stringable\Stringable', $request->string('title', 'default'));

// Request::wantsJson() tests
assertType('bool', $request->wantsJson());

// Request::isSecure() tests
assertType('bool', $request->isSecure());

// Request::getScheme() tests
assertType('string', $request->getScheme());

// Request::getPort() tests
assertType('int', $request->getPort());

// Request::schemeAndHttpHost() tests
assertType('string', $request->schemeAndHttpHost());

// Request::anyFilled() tests
assertType('bool', $request->anyFilled(['key1', 'key2']));
assertType('bool', $request->anyFilled('key'));
