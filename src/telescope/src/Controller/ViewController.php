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

use FriendsOfHyperf\Telescope\Telescope;
use FriendsOfHyperf\Telescope\TelescopeConfig;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

use function Hyperf\Collection\value;

class ViewController
{
    private array $caches = [];

    public function __construct(
        protected ContainerInterface $container,
        protected RequestInterface $request,
        protected ResponseInterface $response,
        protected TelescopeConfig $telescopeConfig
    ) {
    }

    public function index()
    {
        $blade = __DIR__ . '/../../storage/view/index.blade.php';

        if (! isset($this->caches[$blade])) {
            $this->caches[$blade] = value(function () use ($blade) {
                $templateContent = file_get_contents($blade);
                $params = [
                    '{{ $path }}' => $this->telescopeConfig->getPath(),
                    '$telescopeScriptVariables' => json_encode(Telescope::scriptVariables()),
                ];
                return strtr($templateContent, $params);
            });
        }

        return $this->response->html($this->caches[$blade]);
    }

    /**
     * @deprecated since v3.1, will removed at v3.2
     */
    public function show()
    {
        return $this->index();
    }

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
