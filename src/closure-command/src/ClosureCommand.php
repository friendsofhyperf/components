<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ClosureCommand;

use Closure;
use Hyperf\Command\Command;
use Hyperf\Context\Context;
use Psr\Container\ContainerInterface;

class ClosureCommand extends Command
{
    /**
     * @var Closure
     */
    protected $closure;

    /**
     * @var ParameterParser
     */
    private $parameterParser;

    public function __construct(ContainerInterface $container, string $signature, Closure $closure)
    {
        $this->signature = $signature;
        $this->closure = $closure;
        $this->parameterParser = $container->get(ParameterParser::class);

        parent::__construct();
    }

    public function handle()
    {
        Context::set(Input::class, $this->input);
        Context::set(Output::class, $this->output);

        $inputs = array_merge($this->input->getArguments(), $this->input->getOptions());
        $parameters = $this->parameterParser->parseClosureParameters($this->closure, $inputs);

        return \call($this->closure->bindTo($this, $this), $parameters);
    }

    /**
     * @return $this
     */
    public function describe(string $description)
    {
        $this->setDescription($description);

        return $this;
    }
}
