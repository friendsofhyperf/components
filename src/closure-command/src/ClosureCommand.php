<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ClosureCommand;

use Closure;
use Hyperf\Command\Command;
use Hyperf\Context\Context;
use Psr\Container\ContainerInterface;

class ClosureCommand extends Command
{
    private ParameterParser $parameterParser;

    public function __construct(ContainerInterface $container, string $signature, protected Closure $closure)
    {
        $this->signature = $signature;
        $this->parameterParser = $container->get(ParameterParser::class);

        parent::__construct();
    }

    public function handle()
    {
        Context::set(Input::class, $this->input);
        Context::set(Output::class, $this->output);

        $inputs = array_merge($this->input->getArguments(), $this->input->getOptions());
        $parameters = $this->parameterParser->parseClosureParameters($this->closure, $inputs);

        return $this->closure->call($this, ...$parameters);
    }

    public function describe(string $description): self
    {
        $this->setDescription($description);

        return $this;
    }
}
