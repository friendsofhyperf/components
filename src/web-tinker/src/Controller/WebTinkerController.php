<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\WebTinker\Controller;

use FriendsOfHyperf\WebTinker\Tinker;
use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;

class WebTinkerController
{
    protected ?ValidatorFactoryInterface $validatorFactory = null;

    protected ?string $blade = null;

    private array $staticFiles = [];

    private string $staticId;

    public function __construct(
        protected ContainerInterface $container,
        protected ConfigInterface $config,
        protected RequestInterface $request,
        protected ResponseInterface $response
    ) {
        if ($container->has(ValidatorFactoryInterface::class)) {
            $this->validatorFactory = $container->get(ValidatorFactoryInterface::class);
        }
        $this->staticId = uniqid();
    }

    public function index()
    {
        if (! $this->blade) {
            $this->blade = file_get_contents(__DIR__ . '/../../resources/views/web-tinker.blade.php');
        }

        $path = $this->request->input('path') ?: $this->config->get('web-tinker.path', '/tinker');
        $theme = $this->request->input('theme') ?: $this->config->get('web-tinker.theme', 'auto');

        $variables = [
            '{{ $path }}' => $path,
            '{{ $theme }}' => $theme,
            '{{ $id }}' => $this->staticId,
        ];

        $contents = strtr($this->blade, $variables);

        return $this->response->html($contents);
    }

    public function execute(RequestInterface $request)
    {
        if ($this->validatorFactory) {
            $validator = $this->validatorFactory->make(
                $request->all(),
                [
                    'code' => 'required',
                ]
            );

            $validated = $validator->validate();
        } else {
            $validated = $request->all();
        }

        $tinker = make(Tinker::class);

        return $tinker->execute($validated['code']);
    }

    public function renderStaticFile(RequestInterface $request, ResponseInterface $response)
    {
        $file = realpath(sprintf(
            '%s/public/%s',
            __DIR__ . '/../../../',
            $request->route('static')
        ));

        if (! isset($this->staticFiles[$file])) {
            if (! file_exists($file)) {
                return $response->html('')->withStatus(404);
            }
            $this->staticFiles[$file] = [
                'contents' => file_get_contents($file),
                'content-type' => match (true) {
                    str_ends_with($file, '.css') => 'text/css',
                    str_ends_with($file, '.js') => 'application/javascript',
                    default => mime_content_type($file),
                },
            ];
        }

        return $response
            ->raw($this->staticFiles[$file]['contents'])
            ->withHeader('Content-Type', $this->staticFiles[$file]['content-type']);
    }
}
