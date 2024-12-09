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

use FriendsOfHyperf\Telescope\Controller;
use FriendsOfHyperf\Telescope\Middleware\Authorize;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\HttpServer\Router\Router;

class RegisterRoutesListener implements ListenerInterface
{
    public function __construct(
        protected DispatcherFactory $dispatcherFactory, // Don't remove this line
        protected ConfigInterface $config
    ) {
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        $server = $this->config->get('telescope.server', 'http');
        if (! is_string($server)) { // will be removed in v3.2
            $server = 'http';
        }
        $prefix = $this->config->get('telescope.path', '/telescope');
        $middleware = (array) $this->config->get('telescope.middleware', [
            Authorize::class,
        ]);

        Router::addServer($server, function () use ($prefix, $middleware) {
            Router::addGroup($prefix, function () {
                Router::addGroup('/telescope-api', function () {
                    Router::post('/cache', [Controller\CacheController::class, 'index']);
                    Router::get('/cache/{id}', [Controller\CacheController::class, 'show']);

                    Router::post('/client-request', [Controller\ClientRequestController::class, 'index']);
                    Router::get('/client-request/{id}', [Controller\ClientRequestController::class, 'show']);

                    Router::post('/commands', [Controller\CommandsController::class, 'index']);
                    Router::get('/commands/{id}', [Controller\CommandsController::class, 'show']);

                    Router::delete('/entries', [Controller\EntriesController::class, 'destroy']);

                    Router::post('/events', [Controller\EventsController::class, 'index']);
                    Router::get('/events/{event}', [Controller\EventsController::class, 'show']);

                    Router::post('/exceptions', [Controller\ExceptionsController::class, 'index']);
                    Router::put('/exceptions/{id}', [Controller\ExceptionsController::class, 'update']);
                    Router::get('/exceptions/{id}', [Controller\ExceptionsController::class, 'show']);

                    Router::post('/logs', [Controller\LogsController::class, 'index']);
                    Router::get('/logs/{id}', [Controller\LogsController::class, 'show']);

                    Router::post('/queries', [Controller\QueriesController::class, 'index']);
                    Router::get('/queries/{id}', [Controller\QueriesController::class, 'show']);

                    Router::post('/toggle-recording', [Controller\RecordingController::class, 'toggle']);

                    Router::post('/redis', [Controller\RedisController::class, 'index']);
                    Router::get('/redis/{id}', [Controller\RedisController::class, 'show']);

                    Router::post('/requests', [Controller\RequestsController::class, 'index']);
                    Router::get('/requests/{id}', [Controller\RequestsController::class, 'show']);

                    Router::post('/services', [Controller\ServicesController::class, 'index']);
                    Router::get('/services/{id}', [Controller\ServicesController::class, 'show']);
                });

                Router::get('/public/{file}', [Controller\ViewController::class, 'renderStaticFile']);

                Router::get('[/]', [Controller\ViewController::class, 'index']);
                Router::get('/{view}', [Controller\ViewController::class, 'index']);
                Router::get('/{view}/{id}', [Controller\ViewController::class, 'index']);
            }, ['middleware' => $middleware]);
        });
    }
}
