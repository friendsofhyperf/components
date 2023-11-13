<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

#[Controller(server: 'telescope')]
class ViewController
{
    #[Inject]
    protected ContainerInterface $container;

    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected ResponseInterface $response;

    private array $caches = [];

    #[GetMapping(path: '/telescope/{view}')]
    public function index()
    {
        $blade = __DIR__ . '/../../storage/view/index.blade.php';

        if (! isset($this->caches[$blade])) {
            $this->caches[$blade] = file_get_contents($blade);
        }

        return $this->response->html($this->caches[$blade]);
    }

    #[GetMapping(path: '/telescope/{view}/{id}')]
    public function show()
    {
        $blade = __DIR__ . '/../../storage/view/index.blade.php';

        if (! isset($this->caches[$blade])) {
            $this->caches[$blade] = file_get_contents($blade);
        }

        return $this->response->html($this->caches[$blade]);
    }

    #[GetMapping(path: '/vendor/telescope/{file}')]
    public function renderStaticFile(string $file)
    {
        $files = [
            'app.js' => [__DIR__ . '/../../public/telescope/app.js', 'application/javascript'],
            'app.css' => [__DIR__ . '/../../public/telescope/app.css', 'text/css'],
            'app-dark.css' => [__DIR__ . '/../../public/telescope/app-dark.css', 'text/css'],
            'favicon.ico' => [__DIR__ . '/../../public/telescope/favicon.ico', 'image/x-icon'],
        ];

        if (! isset($this->caches[$file])) {
            if (! isset($files[$file]) || ! file_exists($files[$file][0])) {
                return $this->response->raw('')->withStatus(404);
            }

            $this->caches[$file] = (string) file_get_contents($files[$file][0]);
        }

        return $this->response->raw($this->caches[$file])->withHeader('Content-Type', $files[$file][1]);
    }
}
