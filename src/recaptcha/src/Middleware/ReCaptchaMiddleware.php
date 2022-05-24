<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ReCaptcha\Middleware;

use FriendsOfHyperf\ReCaptcha\ReCaptchaManager;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class ReCaptchaMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var HttpResponse
     */
    protected $response;

    /**
     * @var string
     */
    protected $version = 'v3';

    /**
     * @var string
     */
    protected $action;

    /**
     * @var float
     */
    protected $score = 0.34;

    /**
     * @var string
     */
    protected $hostname;

    /**
     * @var string
     */
    protected $inputName = 'g-recaptcha-response';

    /**
     * @var string
     */
    protected $message = 'Google ReCaptcha Verify Fails';

    public function __construct(ContainerInterface $container, HttpResponse $response, RequestInterface $request)
    {
        $this->container = $container;
        $this->response = $response;
        $this->request = $request;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $recaptcha = ReCaptchaManager::get($this->version);

        if ($this->action) {
            $recaptcha->setExpectedAction($this->action);
        }

        if ($this->score) {
            $recaptcha->setScoreThreshold((float) $this->score);
        }

        if ($this->hostname) {
            $recaptcha->setExpectedHostname($this->hostname);
        }

        if ($recaptcha->verify($this->request->input($this->inputName, ''), $this->request->server('remote_addr'))) {
            return $handler->handle($request);
        }

        return $this->response->withStatus(401, $this->message);
    }
}
