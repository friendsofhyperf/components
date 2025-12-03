<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use Closure;
use FriendsOfHyperf\Sentry\Factory\ClientBuilderFactory;
use FriendsOfHyperf\Sentry\Factory\HubFactory;
use FriendsOfHyperf\Sentry\Feature;
use FriendsOfHyperf\Sentry\Integration;
use FriendsOfHyperf\Sentry\Integration\RequestFetcher;
use FriendsOfHyperf\Sentry\Tracing\Tracer;
use FriendsOfHyperf\Sentry\Version;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Sentry\ClientBuilder;
use Sentry\Event;
use Sentry\State\HubInterface;
use Sentry\State\Scope;
use Sentry\Tracing\Span;
use Sentry\Tracing\SpanContext;
use Sentry\Tracing\Transaction;
use Sentry\Tracing\TransactionContext;
use Sentry\Transport\Result;
use Sentry\Transport\ResultStatus;
use Sentry\Transport\TransportInterface;

use function FriendsOfHyperf\Sentry\feature;
use function FriendsOfHyperf\Sentry\startTransaction;
use function FriendsOfHyperf\Sentry\trace;
use function PHPStan\Testing\assertType;

defined('BASE_PATH') or define('BASE_PATH', __DIR__);

final class ArrayConfig implements ConfigInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $items;

    /**
     * @param array<string, mixed> $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->items[$key] ?? $default;
    }

    public function has(string $keys): bool
    {
        return array_key_exists($keys, $this->items);
    }

    public function set(string $key, mixed $value): void
    {
        $this->items[$key] = $value;
    }
}

final class DummyContainer implements ContainerInterface
{
    /**
     * @param array<string, mixed> $entries
     */
    public function __construct(private array $entries)
    {
    }

    public function get(string $id): mixed
    {
        if (! $this->has($id)) {
            throw new RuntimeException(sprintf('Entry "%s" not found.', $id));
        }

        $entry = $this->entries[$id];

        if ($entry instanceof Closure) {
            $entry = $entry($this);
        }

        return $entry;
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->entries);
    }
}

final class DummyTransport implements TransportInterface
{
    public function send(Event $event): Result
    {
        return new Result(ResultStatus::success(), $event);
    }

    public function close(?int $timeout = null): Result
    {
        return new Result(ResultStatus::success());
    }
}

assertType(Feature::class, feature());

$transaction = startTransaction(new TransactionContext('demo'));
assertType(Transaction::class, $transaction);

assertType('bool', trace(static fn (Scope $scope): bool => true, new SpanContext()));

$config = new ArrayConfig([
    'sentry.enable.foo' => true,
    'sentry.breadcrumbs.foo' => false,
    'sentry.tracing_.foo' => true,
    'sentry.tracing_spans.foo' => true,
    'sentry.tracing_tags' => ['foo' => true],
    'sentry.crons.enable' => true,
    'sentry.ignore_exceptions' => [RuntimeException::class],
]);

$feature = new Feature($config);
assertType('bool', $feature->isEnabled('foo'));
assertType('bool', $feature->isBreadcrumbEnabled('foo'));
assertType('bool', $feature->isTracingEnabled('foo'));
assertType('bool', $feature->isTracingSpanEnabled('foo'));
assertType('bool', $feature->isTracingTagEnabled('foo'));
assertType('bool', $feature->isCronsEnabled());

assertType('string', Integration::sentryMeta());
assertType('string', Integration::sentryTracingMeta());
assertType('string', Integration::sentryBaggageMeta());
assertType(Span::class . '|null', Integration::currentTracingSpan());

assertType('string', Version::getSdkIdentifier());
assertType('string', Version::getSdkVersion());

$factoryConfig = new ArrayConfig([
    'sentry' => [
        'integrations' => [],
        'transport_channel_size' => 1,
    ],
]);

$clientBuilderFactoryContainer = new DummyContainer([
    ConfigInterface::class => $factoryConfig,
    TransportInterface::class => new DummyTransport(),
]);

$clientBuilderFactory = new ClientBuilderFactory();
$clientBuilder = $clientBuilderFactory($clientBuilderFactoryContainer);
assertType(ClientBuilder::class, $clientBuilder);

$hubContainer = new DummyContainer([
    ConfigInterface::class => $factoryConfig,
    ClientBuilder::class => $clientBuilder,
    RequestFetcher::class => new RequestFetcher(),
]);

$hubFactory = new HubFactory();
$hub = $hubFactory($hubContainer);
assertType(HubInterface::class, $hub);

$requestFetcher = new RequestFetcher();
assertType(ServerRequestInterface::class . '|null', $requestFetcher->fetchRequest());

$tracer = new Tracer();
assertType(Transaction::class, $tracer->startTransaction(new TransactionContext('demo')));
assertType('string', $tracer->trace(static fn (Scope $scope): string => 'done', new SpanContext()));
