<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope\Listener;

use FriendsOfHyperf\Telescope\IncomingEntry;
use FriendsOfHyperf\Telescope\SwitchManager;
use FriendsOfHyperf\Telescope\Telescope;
use FriendsOfHyperf\Telescope\TelescopeContext;
use Hyperf\Collection\Arr;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\HttpServer\Event\RequestReceived;
use Hyperf\HttpServer\Event\RequestTerminated;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Rpc;
use Hyperf\Server\Event;
use Hyperf\Stringable\Str;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swow\Psr7\Message\ResponsePlusInterface;

use function Hyperf\Collection\collect;
use function Hyperf\Config\config;

class RequestHandledListener implements ListenerInterface
{
    public function __construct(
        protected ContainerInterface $container,
        protected ConfigInterface $config,
        protected SwitchManager $switchManager
    ) {
    }

    public function listen(): array
    {
        return [
            RequestReceived::class,
            RequestTerminated::class,
        ];
    }

    public function process(object $event): void
    {
        if (! $this->switchManager->isEnable('request')) {
            return;
        }
        match ($event::class) {
            RequestReceived::class => $this->requestReceived($event),
            RequestTerminated::class => $this->requestHandled($event),
            default => '', // fix phpstan error
        };
    }

    public function requestReceived(RequestReceived $event)
    {
        $request = $event->request;

        if (! $batchId = $request->getHeaderLine('batch-id')) {
            $batchId = $this->getRpcBatchId();
        }

        if ($batchId) {
            $subBatchId = Str::orderedUuid()->toString();
            TelescopeContext::setSubBatchId($subBatchId);
        } else {
            $batchId = Str::orderedUuid()->toString();
        }

        TelescopeContext::setBatchId($batchId);
    }

    public function requestHandled(RequestTerminated $event)
    {
        if (
            $event->response instanceof ResponsePlusInterface
            && $batchId = TelescopeContext::getBatchId()
        ) {
            $event->response->addHeader('batch-id', $batchId);
        }

        $psr7Request = $event->request;
        $psr7Response = $event->response;
        $middlewares = $this->config->get('middlewares.' . ($event->server ?? 'http'), []);
        $middlewares = collect($middlewares)->map(function ($priority, $middleware) {
            return is_int($middleware) ? $priority : $middleware;
        })->values()->all();
        $startTime = $psr7Request->getServerParams()['request_time_float'];

        if ($this->incomingRequest($psr7Request)) {
            /** @var Dispatched $dispatched */
            $dispatched = $psr7Request->getAttribute(Dispatched::class);
            $serverName = $dispatched->serverName ?? 'http';

            $entry = IncomingEntry::make([
                'ip_address' => $psr7Request->getServerParams()['remote_addr'] ?? 'unknown',
                'uri' => $psr7Request->getRequestTarget(),
                'method' => $psr7Request->getMethod(),
                'controller_action' => $dispatched->handler ? $dispatched->handler->callback : '',
                'middleware' => $middlewares,
                'headers' => $psr7Request->getHeaders(),
                'payload' => $psr7Request->getParsedBody(),
                'session' => '',
                'response_status' => $psr7Response->getStatusCode(),
                'response' => $this->response($psr7Response),
                'duration' => $startTime ? floor((microtime(true) - $startTime) * 1000) : null,
                'memory' => round(memory_get_peak_usage(true) / 1024 / 1025, 1),
            ]);

            $serverConfig = collect(config('server.servers'))->firstWhere('name', $serverName);
            $handlerClass = $serverConfig['callbacks'][Event::ON_RECEIVE][0] ?? $serverConfig['callbacks'][Event::ON_REQUEST][0] ?? null;
            $handler = is_string($handlerClass) && $this->container->has($handlerClass) ? $this->container->get($handlerClass) : null;

            if ($handler && ($handler instanceof \Hyperf\RpcServer\Server || $handler instanceof \Hyperf\GrpcServer\Server || $handler instanceof \Hyperf\JsonRpc\HttpServer)) {
                Telescope::recordService($entry);
            } else {
                Telescope::recordRequest($entry);
            }
        }
    }

    protected function incomingRequest(ServerRequestInterface $psr7Request): bool
    {
        $target = $psr7Request->getRequestTarget();

        if (Str::contains($target, '.ico')) {
            return false;
        }

        if (Str::contains($target, 'telescope') !== false) {
            return false;
        }

        return true;
    }

    protected function response(ResponseInterface $response): string|array
    {
        $stream = $response->getBody();

        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        $content = $stream->getContents();

        if (is_string($content)) {
            if (! $this->contentWithinLimits($content)) {
                return 'Purged By Hyperf Telescope';
            }
            if (
                is_array(json_decode($content, true))
                && json_last_error() === JSON_ERROR_NONE
            ) {
                return $this->contentWithinLimits($content)
                ? $this->hideParameters(json_decode($content, true), Telescope::$hiddenResponseParameters)
                : 'Purged By Hyperf Telescope';
            }
            if (Str::startsWith(strtolower($response->getHeaderLine('content-type') ?? ''), 'text/plain')) {
                return $this->contentWithinLimits($content) ? $content : 'Purged By Hyperf Telescope';
            }
            if (Str::contains($response->getHeaderLine('content-type'), 'application/grpc') !== false) {
                // to do for grpc
                return 'Purged By Hyperf Telescope';
            }
        }

        if (empty($content)) {
            return 'Empty Response';
        }

        return 'HTML Response';
    }

    protected function contentWithinLimits(string $content): bool
    {
        $limit = 64;
        return mb_strlen($content) / 1000 <= $limit;
    }

    /**
     * Hide the given parameters.
     */
    protected function hideParameters(array $data, array $hidden): array
    {
        foreach ($hidden as $parameter) {
            if (Arr::get($data, $parameter)) {
                Arr::set($data, $parameter, '********');
            }
        }

        return $data;
    }

    protected function getRpcBatchId(): string
    {
        $carrier = $this->getRpcContext();
        return $carrier['batch-id'] ?? '';
    }

    protected function getRpcContext(): array
    {
        $container = ApplicationContext::getContainer();
        if (! $container->has(Rpc\Context::class)) {
            return [];
        }
        $rpcContext = $container->get(Rpc\Context::class);
        return (array) $rpcContext->get('telescope.carrier');
    }
}
