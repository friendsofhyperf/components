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

use Hyperf\Command\Command;
use Psr\Container\ContainerInterface;

class AnnotationCommand extends Command
{
    private string $class;

    private string $method;

    private ParameterParser $parameterParser;

    public function __construct(ContainerInterface $container, string $signature, string $class, string $method, string $description = '')
    {
        $this->signature = $signature;
        $this->description = $description;
        $this->class = $class;
        $this->method = $method;
        $this->parameterParser = $container->get(ParameterParser::class);

        parent::__construct();
    }

    public function handle()
    {
        $inputs = array_merge($this->input->getArguments(), $this->input->getOptions());
        $parameters = $this->parameterParser->parseMethodParameters($this->class, $this->method, $inputs);

        return \call($this->class, $parameters);
    }
}
