<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\PrettyConsole\Traits;

use FriendsOfHyperf\PrettyConsole\View\Components\Factory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait Prettyable
{
    protected ?Factory $components = null;

    protected function setUpPrettyable(?InputInterface $input, OutputInterface $output): void
    {
        $this->components ??= new Factory($output);
    }
}
