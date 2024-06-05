<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Mail\Command;

use Hyperf\Devtool\Generator\GeneratorCommand;

class MailCommand extends GeneratorCommand
{
    protected ?string $name = 'gen:mail';

    protected string $description = 'Generate a new mail class';

    protected function getStub(): string
    {
        return $this->getConfig()['stub'] ?? ($this->input->getOption('markdown') !== false ? __DIR__ . '/stubs/mail.stub' : __DIR__ . '/stubs/markdown.stub');
    }

    protected function getDefaultNamespace(): string
    {
        return $this->getConfig()['namespace'] ?? 'App\Mail';
    }
}
