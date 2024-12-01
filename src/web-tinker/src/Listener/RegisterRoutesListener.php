<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\WebTinker\Listener;

use FriendsOfHyperf\WebTinker\Http\Controllers\WebTinkerController;
use FriendsOfHyperf\WebTinker\Http\Middleware\Authorize;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\HttpServer\Router\Router;

class RegisterRoutesListener implements ListenerInterface
{
    public function __construct(
        protected DispatcherFactory $dispatcherFactory,
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
        $prefix = $this->config->get('web-tinker.path', '/tinker');

        Router::addGroup($prefix, function () {
            Router::get('', WebTinkerController::class . '@index');
            Router::post('', WebTinkerController::class . '@execute');
            Router::get('/public/{static}', WebTinkerController::class . '@renderStaticFile');
        }, ['middleware' => [Authorize::class]]);
    }
}
