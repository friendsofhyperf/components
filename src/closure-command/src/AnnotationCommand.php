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

use Hyperf\Command\Command;
use Hyperf\Context\Context;
use Psr\Container\ContainerInterface;

class AnnotationCommand extends Command
{
    private ParameterParser $parameterParser;

    public function __construct(private ContainerInterface $container, protected ?string $signature, private string $class, private string $method, string $description = '')
    {
        $this->parameterParser = $container->get(ParameterParser::class);

        parent::__construct();
        parent::setDescription($description);
    }

    public function handle()
    {
        Context::set(Input::class, $this->input);
        Context::set(Output::class, $this->output);

        $inputs = array_merge($this->input->getArguments(), $this->input->getOptions());
        $parameters = $this->parameterParser->parseMethodParameters($this->class, $this->method, $inputs);

        return (fn ($method) => $this->{$method}(...$parameters))->call($this->container->get($this->class), $this->method);
    }
}
