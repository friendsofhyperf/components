<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ReCaptcha\Middleware;

use FriendsOfHyperf\ReCaptcha\ReCaptchaManager;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponseInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @property \Psr\Http\Message\ResponseInterface $response
 */
abstract class ReCaptchaMiddleware implements MiddlewareInterface
{
    protected string $version = 'v3';

    protected string $action;

    protected float $score = 0.34;

    protected string $hostname;

    protected string $inputName = 'g-recaptcha-response';

    protected int $responseCode = 401;

    protected string $responseMessage = 'Google ReCaptcha Verify Fails';

    protected ReCaptchaManager $manager;

    public function __construct(
        protected ContainerInterface $container,
        protected RequestInterface $request,
        protected HttpResponseInterface $response
    ) {
        $this->manager = $container->get(ReCaptchaManager::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $recaptcha = $this->manager->get($this->version);

        if ($this->action) {
            $recaptcha->setExpectedAction($this->action);
        }

        if ($this->score) {
            $recaptcha->setScoreThreshold((float) $this->score);
        }

        if ($this->hostname) {
            $recaptcha->setExpectedHostname($this->hostname);
        }

        if ($recaptcha->verify($this->request->input($this->inputName, ''), $this->request->server('remote_addr'))->isSuccess()) {
            return $handler->handle($request);
        }

        return $this->response->withStatus($this->responseCode, $this->responseMessage);
    }
}
