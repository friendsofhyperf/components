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
                    Router::post('/cache', [Controller\CacheController::class, 'list']);
                    Router::get('/cache/{id}', [Controller\CacheController::class, 'detail']);

                    Router::post('/client-request', [Controller\ClientRequestController::class, 'list']);
                    Router::get('/client-request/{id}', [Controller\ClientRequestController::class, 'detail']);

                    Router::post('/commands', [Controller\CommandsController::class, 'list']);
                    Router::get('/commands/{id}', [Controller\CommandsController::class, 'detail']);

                    Router::delete('/entries', [Controller\EntriesController::class, 'destroy']);

                    Router::post('/events', [Controller\EventsController::class, 'list']);
                    Router::get('/events/{event}', [Controller\EventsController::class, 'detail']);

                    Router::post('/exceptions', [Controller\ExceptionsController::class, 'list']);
                    Router::get('/exceptions/{id}', [Controller\ExceptionsController::class, 'detail']);

                    Router::post('/logs', [Controller\LogsController::class, 'list']);
                    Router::get('/logs/{id}', [Controller\LogsController::class, 'detail']);

                    Router::post('/queries', [Controller\QueriesController::class, 'list']);
                    Router::get('/queries/{id}', [Controller\QueriesController::class, 'detail']);

                    Router::post('/toggle-recording', [Controller\RecordingController::class, 'toggle']);

                    Router::post('/redis', [Controller\RedisController::class, 'list']);
                    Router::get('/redis/{id}', [Controller\RedisController::class, 'detail']);

                    Router::post('/requests', [Controller\RequestsController::class, 'list']);
                    Router::get('/requests/{id}', [Controller\RequestsController::class, 'detail']);

                    Router::post('/services', [Controller\ServicesController::class, 'list']);
                    Router::get('/services/{id}', [Controller\ServicesController::class, 'detail']);
                });

                Router::get('/public/{file}', [Controller\ViewController::class, 'renderStaticFile']);

                Router::get('[/]', [Controller\ViewController::class, 'index']);
                Router::get('/{view}', [Controller\ViewController::class, 'index']);
                Router::get('/{view}/{id}', [Controller\ViewController::class, 'index']);
            }, ['middleware' => $middleware]);
        });
    }
}
