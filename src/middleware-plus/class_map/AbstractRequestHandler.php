<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */

namespace Hyperf\Dispatcher;

use Hyperf\Dispatcher\Exceptions\InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;

use function is_string;

abstract class AbstractRequestHandler
{
    protected int $offset = 0;

    /**
     * @param array $middlewares All middlewares to dispatch by dispatcher
     * @param MiddlewareInterface|object $coreHandler The core middleware of dispatcher
     */
    public function __construct(protected array $middlewares, protected $coreHandler, protected ContainerInterface $container)
    {
        $this->middlewares = array_values($this->middlewares);
    }

    protected function handleRequest($request)
    {
        $arguments = [];
        if (! isset($this->middlewares[$this->offset])) {
            $handler = $this->coreHandler;
        } else {
            $handler = $this->middlewares[$this->offset];
            if (is_string($handler)) {
                if (strpos($handler, ':') !== false) {
                    [$handler, $arguments] = explode(':', $handler, 2);
                    $arguments = explode(',', $arguments);
                }
                $handler = $this->container->get($handler);
            }
        }
        if (! $handler || ! method_exists($handler, 'process')) {
            throw new InvalidArgumentException('Invalid middleware, it has to provide a process() method.');
        }
        return $handler->process($request, $this->next(), ...$arguments);
    }

    protected function next(): self
    {
        ++$this->offset;
        return $this;
    }
}
