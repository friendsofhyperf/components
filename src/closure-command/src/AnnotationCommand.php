<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ClosureCommand;

use Hyperf\Command\Command;
use Hyperf\Command\Concerns\InteractsWithIO;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\class_uses_recursive;

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
        $inputs = array_merge($this->input->getArguments(), $this->input->getOptions());
        $parameters = $this->parameterParser->parseMethodParameters($this->class, $this->method, $inputs);
        $instance = $this->container->get($this->class);

        if (in_array(InteractsWithIO::class, class_uses_recursive($instance))) {
            $instance->setInput($this->input);
            $instance->setOutput($this->output);
        }

        return (fn ($method) => $this->{$method}(...$parameters))->call($instance, $this->method);
    }
}
