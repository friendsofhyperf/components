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
use Hyperf\Context\Context;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Style\SymfonyStyle;

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
        Context::set(SymfonyStyle::class, $this->output);

        $inputs = array_merge($this->input->getArguments(), $this->input->getOptions());
        $parameters = $this->parameterParser->parseMethodParameters($this->class, $this->method, $inputs);

        return \call([$this->container->get($this->class), $this->method], $parameters);
    }
}
