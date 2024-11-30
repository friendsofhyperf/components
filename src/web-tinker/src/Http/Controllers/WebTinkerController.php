<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\WebTinker\Http\Controllers;

use FriendsOfHyperf\WebTinker\Tinker;
use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use PSpell\Config;

use function Hyperf\Config\config;
use function Hyperf\ViewEngine\view;

class WebTinkerController
{
    public function __construct(
        protected ConfigInterface $config,
        protected ValidatorFactoryInterface $validatorFactory
    ) {
    }

    public function index()
    {
        return view('web-tinker::web-tinker', [
            'path' => $this->config->get('web-tinker.path'),
            'theme' => $this->config->get('web-tinker.theme'),
        ]);
    }

    public function execute(RequestInterface $request, Tinker $tinker)
    {
        $validator = $this->validatorFactory->make(
            $request->all(),
            [
                'code' => 'required',
            ]
        );

        $validated = $validator->validate();

        return $tinker->execute($validated['code']);
    }
}
